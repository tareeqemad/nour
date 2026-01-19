<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    /**
     * الحصول على بيانات النموذج للمولد (للفني)
     */
    public function getFormData(Generator $generator): JsonResponse
    {
        $user = request()->user();

        // التحقق من أن المستخدم فني
        if (!$user->isTechnician()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        // التحقق من أن المولد مرتبط بمشغل المستخدم
        if (!$this->canAccessGenerator($user, $generator)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول لهذا المولد.',
            ], 403);
        }

        // جلب ثوابت نوع الصيانة
        $maintenanceTypes = \App\Helpers\ConstantsHelper::get(12); // نوع الصيانة

        return response()->json([
            'success' => true,
            'data' => [
                'generator' => [
                    'id' => $generator->id,
                    'name' => $generator->name,
                    'generator_number' => $generator->generator_number,
                    'operator' => $generator->operator ? [
                        'id' => $generator->operator->id,
                        'name' => $generator->operator->name,
                    ] : null,
                ],
                'maintenance_types' => $maintenanceTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'label' => $type->label,
                        'code' => $type->code,
                    ];
                }),
            ],
        ]);
    }

    /**
     * حفظ سجل صيانة جديد
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // التحقق من أن المستخدم فني
        if (!$user->isTechnician()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        $validated = $request->validate([
            'generator_id' => ['required', 'exists:generators,id'],
            'maintenance_type_id' => ['required', 'exists:constant_details,id'],
            'next_maintenance_type_id' => ['nullable', 'exists:constant_details,id'],
            'maintenance_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'technician_name' => ['nullable', 'string', 'max:255'],
            'work_performed' => ['nullable', 'string'],
            'downtime_hours' => ['nullable', 'numeric', 'min:0'],
            'parts_cost' => ['nullable', 'numeric', 'min:0'],
            'labor_hours' => ['nullable', 'numeric', 'min:0'],
            'labor_rate_per_hour' => ['nullable', 'numeric', 'min:0'],
            'maintenance_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $generator = Generator::find($validated['generator_id']);

        // التحقق من الصلاحيات
        if (!$this->canAccessGenerator($user, $generator)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول لهذا المولد.',
            ], 403);
        }

        // حساب downtime_hours من start_time و end_time
        if (isset($validated['start_time']) && isset($validated['end_time']) 
            && !empty($validated['start_time']) && !empty($validated['end_time'])) {
            try {
                $startTime = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
                $endTime = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
                
                if ($endTime < $startTime) {
                    $endTime->addDay();
                }
                
                $diffInMinutes = $startTime->diffInMinutes($endTime);
                $diffInHours = $diffInMinutes / 60;
                $validated['downtime_hours'] = round($diffInHours, 2);
            } catch (\Exception $e) {
                $validated['downtime_hours'] = null;
            }
        } else {
            $validated['downtime_hours'] = null;
        }

        // حساب maintenance_cost
        if (isset($validated['parts_cost']) && isset($validated['labor_hours']) 
            && isset($validated['labor_rate_per_hour'])) {
            $partsCost = $validated['parts_cost'] ?? 0;
            $laborHours = $validated['labor_hours'] ?? 0;
            $laborRate = $validated['labor_rate_per_hour'] ?? 0;
            $validated['maintenance_cost'] = round($partsCost + ($laborHours * $laborRate), 2);
        } else {
            $validated['maintenance_cost'] = null;
        }

        DB::beginTransaction();
        try {
            $maintenanceRecord = MaintenanceRecord::create($validated);

            // تحديث تاريخ آخر صيانة للمولد إذا كانت صيانة دورية
            if ($generator && $maintenanceRecord->maintenance_type_id) {
                $periodicConstant = \App\Helpers\ConstantsHelper::findByCode(12, 'PERIODIC');
                if ($periodicConstant && (int)$maintenanceRecord->maintenance_type_id === $periodicConstant->id) {
                    $generator->update([
                        'last_major_maintenance_date' => $maintenanceRecord->maintenance_date
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ سجل الصيانة بنجاح.',
                'data' => [
                    'maintenance_record' => [
                        'id' => $maintenanceRecord->id,
                        'generator_id' => $maintenanceRecord->generator_id,
                        'maintenance_date' => $maintenanceRecord->maintenance_date->format('Y-m-d'),
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
     * قائمة سجلات الصيانة
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTechnician()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        // تضمين المولدات المحذوفة soft delete في السجلات التاريخية
        $query = MaintenanceRecord::with([
            'generator.operator', // العلاقة generator تستخدم withTrashed() بشكل افتراضي
            'maintenanceTypeDetail',
            'nextMaintenanceTypeDetail'
        ]);

        // تصفية حسب المولدات المتاحة للمستخدم
        $operatorIds = $user->operators()->pluck('operators.id');
        $generatorIds = Generator::whereIn('operator_id', $operatorIds)->pluck('id');
        $query->whereIn('generator_id', $generatorIds);

        // تصفية حسب المولد (اختياري)
        if ($request->filled('generator_id')) {
            $query->where('generator_id', $request->input('generator_id'));
        }

        // تصفية حسب التاريخ (اختياري)
        if ($request->filled('date_from')) {
            $query->whereDate('maintenance_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('maintenance_date', '<=', $request->input('date_to'));
        }

        $records = $query->orderBy('maintenance_date', 'desc')
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
     * عرض سجل صيانة محدد
     */
    public function show(MaintenanceRecord $maintenanceRecord): JsonResponse
    {
        $user = request()->user();

        if (!$user->isTechnician()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول.',
            ], 403);
        }

        $maintenanceRecord->load(['generator.operator', 'maintenanceTypeDetail', 'nextMaintenanceTypeDetail']);

        // التحقق من الصلاحيات
        if (!$this->canAccessGenerator($user, $maintenanceRecord->generator)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول لهذا السجل.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'maintenance_record' => $maintenanceRecord,
            ],
        ]);
    }

    /**
     * التحقق من إمكانية الوصول للمولد
     */
    private function canAccessGenerator($user, Generator $generator): bool
    {
        if (!$generator->operator_id) {
            return false;
        }

        $operatorIds = $user->operators()->pluck('operators.id');
        return $operatorIds->contains($generator->operator_id);
    }
}
