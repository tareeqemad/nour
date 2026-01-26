<?php

namespace App\Services;

use App\Models\ComplaintSuggestion;
use App\Models\Generator;
use App\Models\Notification;
use App\Models\Operator;
use App\Governorate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComplaintSuggestionService
{
    /**
     * إنشاء شكوى/مقترح جديد
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $image
     * @return ComplaintSuggestion
     */
    public function createComplaint(array $data, ?UploadedFile $image = null): ComplaintSuggestion
    {
        DB::beginTransaction();
        try {
            // رفع الصورة إن وجدت
            $imagePath = $this->handleImageUpload($image);
            
            // تحديد المشغل
            $operatorId = $this->determineOperator($data);
            
            // إنشاء الشكوى/المقترح
            $complaintSuggestion = ComplaintSuggestion::create([
                'type' => $data['type'],
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'governorate' => Governorate::from($data['governorate']),
                'operator_id' => $operatorId,
                'generator_id' => $data['generator_id'] ?? null,
                'subject' => $data['message'],
                'message' => $data['message'],
                'image' => $imagePath,
                'tracking_code' => ComplaintSuggestion::generateTrackingCode(),
                'status' => 'pending',
                'closed_by_operator' => false,
            ]);
            
            // إرسال الإشعارات
            $this->sendNotifications($complaintSuggestion, $data);
            
            DB::commit();
            
            return $complaintSuggestion;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // حذف الصورة إذا فشل الحفظ
            if (isset($imagePath) && $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            
            throw $e;
        }
    }
    
    /**
     * رفع الصورة
     *
     * @param UploadedFile|null $image
     * @return string|null
     */
    private function handleImageUpload(?UploadedFile $image): ?string
    {
        if (!$image) {
            return null;
        }
        
        return $image->store('complaints-suggestions', 'public');
    }
    
    /**
     * تحديد المشغل بناءً على البيانات
     *
     * @param array<string, mixed> $data
     * @return int|null
     */
    private function determineOperator(array $data): ?int
    {
        // إذا تم اختيار مولد محدد، نأخذ المشغل من المولد
        if (!empty($data['generator_id'])) {
            $generator = Generator::with('generationUnit.operator')->find($data['generator_id']);
            
            if ($generator) {
                // البحث عن المشغل من خلال generation_unit (الطريقة المفضلة)
                if ($generator->generationUnit && $generator->generationUnit->operator) {
                    return $generator->generationUnit->operator->id;
                }
                
                // Fallback: إذا لم يكن هناك generation_unit، نستخدم operator_id المباشر
                if ($generator->operator_id) {
                    return $generator->operator_id;
                }
            }
        }
        
        // إذا لم يتم اختيار مولد، نتركه null
        // وسيتم ربطه لاحقاً من قبل Admin/Energy Authority
        return null;
    }
    
    /**
     * إرسال الإشعارات للمستخدمين المعنيين
     *
     * @param ComplaintSuggestion $complaintSuggestion
     * @param array<string, mixed> $data
     * @return void
     */
    private function sendNotifications(ComplaintSuggestion $complaintSuggestion, array $data): void
    {
        // إذا كان هناك مشغل محدد، إرسال إشعارات
        if (!$complaintSuggestion->operator_id) {
            return;
        }
        
        $operator = Operator::find($complaintSuggestion->operator_id);
        if (!$operator) {
            return;
        }
        
        $typeLabel = $data['type'] === 'complaint' ? 'شكوى' : 'مقترح';
        
        // إرسال إشعارات لـ Admin, SuperAdmin, Energy Authority
        Notification::notifyOperatorApprovers(
            'complaint_new',
            'شكوى/مقترح جديد على مشغل',
            "تم إرسال {$typeLabel} جديد على المشغل: {$operator->name} من {$data['name']}",
            route('admin.complaints-suggestions.show', $complaintSuggestion)
        );
        
        // إرسال إشعار للمشغل
        Notification::notifyOperatorUsers(
            $operator,
            'complaint_assigned',
            'شكوى/مقترح جديد',
            "تم إرسال {$typeLabel} جديد متعلق بمشغلك",
            route('admin.complaints-suggestions.show', $complaintSuggestion)
        );
    }
}
