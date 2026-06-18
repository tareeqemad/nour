<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOperatorProfileRequest;
use App\Models\GenerationUnit;
use App\Models\OperatorAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OperatorProfileController extends Controller
{
    public function show(Request $request, ?\App\Models\Operator $operator = null): View
    {
        $user = auth()->user();

        // السوبر أدمن يمكنه رؤية ملف أي مشغل
        if ($user->isSuperAdmin() || $user->isEnergyAuthority()) {
            // إذا تم تمرير operator_id في الـ request
            $operatorId = $request->query('operator_id');
            if ($operatorId) {
                $operator = \App\Models\Operator::findOrFail($operatorId);
            } elseif ($operator) {
                // إذا تم تمرير operator كـ route parameter
                $operator = $operator;
            } else {
                // إذا لم يتم تحديد مشغل، نعرض رسالة خطأ
                abort(404, 'يرجى تحديد المشغل');
            }
        } elseif ($user->isCompanyOwner()) {
            // المشغل يشوف ملفه فقط
            $operator = $user->ownedOperators()->first();
            if (! $operator) {
                // إذا لم يكن لديه مشغل، نعرض الصفحة بدون بيانات
                $generationUnits = collect();
                return view('admin.operators.profile', compact('operator', 'generationUnits'));
            }
        } else {
            abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة');
        }

        // جلب وحدات التوليد للمشغل
        $operator->load(['attachments.uploader']);

        $generationUnits = $operator->generationUnits()
            ->with('statusDetail')
            ->withCount('generators as actual_generators_count')
            ->get();

        return view('admin.operators.profile', compact('operator', 'generationUnits'));
    }

    public function update(UpdateOperatorProfileRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner()) {
            abort(403);
        }

        $operator = $user->ownedOperators()->first();
        
        // إذا لم يكن المشغل موجوداً، ننشئه
        if (! $operator) {
            $data = $request->validated();
            
            // التحقق من اكتمال البيانات: يجب إدخال الحقول الأربعة
            $isComplete = !empty($data['name']) 
                && !empty($data['owner_name']) 
                && !empty($data['owner_id_number']) 
                && !empty($data['operator_id_number']);
            
            // إنشاء المشغل الجديد
            $operator = \App\Models\Operator::create([
                'owner_id' => $user->id,
                'name' => $data['name'] ?? '',
                'owner_name' => $data['owner_name'] ?? '',
                'owner_id_number' => $data['owner_id_number'] ?? '',
                'operator_id_number' => $data['operator_id_number'] ?? '',
                'territory_radius_km' => $data['territory_radius_km'] ?? 50.0,
                'phone' => $user->phone,
                'profile_completed' => $isComplete,
                'is_approved' => false, // المشغل الجديد غير معتمد
            ]);
            
            // ربط المشغل بالمستخدم
            $user->operators()->attach($operator->id);
        } else {
            $data = $request->validated();

            // تحديث profile_completed تلقائياً إذا كانت البيانات مكتملة
            // يجب إدخال الحقول الأربعة: name, owner_name, owner_id_number, operator_id_number
            $isComplete = !empty($data['name']) 
                && !empty($data['owner_name']) 
                && !empty($data['owner_id_number']) 
                && !empty($data['operator_id_number']);
            $data['profile_completed'] = $isComplete;

            $operator->update($data);
        }

        // تحديث الـ cache إذا كان موجوداً
        $operator->refresh();

        $msg = $operator->wasRecentlyCreated ? 'تم إنشاء ملف المشغل بنجاح ✅' : 'تم حفظ بيانات المشغل بنجاح ✅';

        // AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'operator' => [
                    'name' => $operator->name,
                    'owner_name' => $operator->owner_name,
                    'owner_id_number' => $operator->owner_id_number,
                    'operator_id_number' => $operator->operator_id_number,
                ],
            ]);
        }

        return redirect()->route('admin.operators.profile')->with('success', $msg);
    }

    public function storeAttachment(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner()) {
            abort(403);
        }

        $operator = $user->ownedOperators()->firstOrFail();

        $validated = $request->validate([
            'attachment_name' => ['required', 'string', 'max:255'],
            'attachment_file' => ['required', 'file', 'max:20480'],
        ], [
            'attachment_name.required' => 'اسم المرفق مطلوب.',
            'attachment_file.required' => 'ملف المرفق مطلوب.',
            'attachment_file.max' => 'حجم الملف يجب أن لا يتجاوز 20MB.',
        ]);

        $file = $validated['attachment_file'];
        $path = $file->store('operator-attachments/' . $operator->id, 'public');

        $operator->attachments()->create([
            'uploaded_by' => $user->id,
            'name' => $validated['attachment_name'],
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->route('admin.operators.profile')
            ->with('success', 'تمت إضافة المرفق بنجاح.');
    }

    public function downloadAttachment(OperatorAttachment $attachment)
    {
        $this->authorize('view', $attachment->operator);

        if (! Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $attachment->file_path,
            $attachment->original_filename ?: $attachment->name
        );
    }

    public function destroyAttachment(OperatorAttachment $attachment): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isCompanyOwner() || ! $user->ownsOperator($attachment->operator)) {
            abort(403);
        }

        $attachment->delete();

        return redirect()->route('admin.operators.profile')
            ->with('success', 'تم حذف المرفق بنجاح.');
    }
}
