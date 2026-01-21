<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOperatorProfileRequest;
use App\Models\GenerationUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $generationUnits = $operator->generationUnits()->with('statusDetail')->withCount('generators')->get();

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
}
