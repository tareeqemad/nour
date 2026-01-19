<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ComplianceSafety;
use App\Models\Generator;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplianceSafetyController extends Controller
{
    /**
     * الحصول على بيانات النموذج للمولد (لدفاع مدني)
     */
    public function getFormData(Generator $generator): JsonResponse
    {
        $user = request()->user();

        // التحقق من أن المستخدم دفاع مدني
        if (!$this->isCivilDefense($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        if (!$generator->operator_id) {
            return response()->json([
                'success' => false,
                'message' => 'المولد غير مرتبط بمشغل.',
            ], 400);
        }

        $operator = $generator->operator;

        // جلب ثوابت حالة شهادة السلامة
        $safetyCertificateStatuses = \App\Helpers\ConstantsHelper::get(13); // حالة شهادة السلامة

        return response()->json([
            'success' => true,
            'data' => [
                'generator' => [
                    'id' => $generator->id,
                    'name' => $generator->name,
                    'generator_number' => $generator->generator_number,
                ],
                'operator' => [
                    'id' => $operator->id,
                    'name' => $operator->name,
                ],
                'safety_certificate_statuses' => $safetyCertificateStatuses->map(function ($status) {
                    return [
                        'id' => $status->id,
                        'label' => $status->label,
                        'code' => $status->code,
                    ];
                }),
            ],
        ]);
    }

    /**
     * حفظ سجل وقاية وسلامة جديد
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // التحقق من أن المستخدم دفاع مدني
        if (!$this->isCivilDefense($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        $validated = $request->validate([
            'generator_id' => ['required', 'exists:generators,id'],
            'safety_certificate_status_id' => ['required', 'exists:constant_details,id'],
            'last_inspection_date' => ['nullable', 'date'],
            'inspection_authority' => ['nullable', 'string', 'max:255'],
            'inspection_result' => ['nullable', 'string'],
            'violations' => ['nullable', 'string'],
        ]);

        $generator = Generator::find($validated['generator_id']);

        if (!$generator->operator_id) {
            return response()->json([
                'success' => false,
                'message' => 'المولد غير مرتبط بمشغل.',
            ], 400);
        }

        // استخدام operator_id من المولد
        $validated['operator_id'] = $generator->operator_id;
        unset($validated['generator_id']); // إزالة generator_id لأنه غير موجود في جدول compliance_safeties

        DB::beginTransaction();
        try {
            $complianceSafety = ComplianceSafety::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ سجل الوقاية والسلامة بنجاح.',
                'data' => [
                    'compliance_safety' => [
                        'id' => $complianceSafety->id,
                        'operator_id' => $complianceSafety->operator_id,
                        'last_inspection_date' => $complianceSafety->last_inspection_date 
                            ? $complianceSafety->last_inspection_date->format('Y-m-d') 
                            : null,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ السجل.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * قائمة سجلات الوقاية والسلامة
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$this->isCivilDefense($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        $query = ComplianceSafety::with(['operator', 'safetyCertificateStatusDetail']);

        // تصفية حسب المشغل (اختياري)
        if ($request->filled('operator_id')) {
            $query->where('operator_id', $request->input('operator_id'));
        }

        // تصفية حسب التاريخ (اختياري)
        if ($request->filled('date_from')) {
            $query->whereDate('last_inspection_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('last_inspection_date', '<=', $request->input('date_to'));
        }

        $records = $query->orderBy('last_inspection_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => [
                'records' => $records->items(),
                'pagination' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ],
            ],
        ]);
    }

    /**
     * عرض سجل وقاية وسلامة محدد
     */
    public function show(ComplianceSafety $complianceSafety): JsonResponse
    {
        $user = request()->user();

        if (!$this->isCivilDefense($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        $complianceSafety->load(['operator', 'safetyCertificateStatusDetail']);

        return response()->json([
            'success' => true,
            'data' => [
                'compliance_safety' => $complianceSafety,
            ],
        ]);
    }

    /**
     * التحقق من أن المستخدم هو دفاع مدني
     */
    private function isCivilDefense($user): bool
    {
        $roleName = $user->roleModel?->name ?? $user->role?->value;
        
        $hasPermission = method_exists($user, 'hasPermission') 
            ? $user->hasPermission('compliance_safety.create') 
            : false;
        
        return $roleName === 'civil_defense' 
            || $user->isEnergyAuthority() 
            || $hasPermission;
    }
}
