<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstantDetailRequest;
use App\Http\Requests\Admin\UpdateConstantDetailRequest;
use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use App\Helpers\ConstantsHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ConstantDetailController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConstantDetailRequest $request): RedirectResponse|JsonResponse
    {
        $detail = ConstantDetail::create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة التفصيل بنجاح.',
                'data' => [
                    'id' => $detail->id,
                    'label' => $detail->label,
                    'code' => $detail->code,
                    'value' => $detail->value,
                ]
            ]);
        }

        return redirect()->back()
            ->with('success', 'تم إضافة التفصيل بنجاح.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConstantDetailRequest $request, ConstantDetail $constantDetail): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        
        // إذا لم يتم إرسال constant_master_id، استخدم القيمة الحالية
        if (!isset($validated['constant_master_id'])) {
            $validated['constant_master_id'] = $constantDetail->constant_master_id;
        }
        
        $constantDetail->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التفصيل بنجاح.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'تم تحديث التفصيل بنجاح.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConstantDetail $constantDetail): RedirectResponse|JsonResponse
    {
        // التحقق من أن الثابت غير مستخدم في أي جدول
        $tables = [
            ['table' => 'generation_units', 'columns' => ['status_id', 'operation_entity_id', 'governorate_id', 'city_id', 'synchronization_available_id', 'environmental_compliance_status_id']],
            ['table' => 'generators', 'columns' => ['status_id', 'engine_type_id', 'injection_system_id', 'measurement_indicator_id', 'technical_condition_id', 'control_panel_type_id', 'control_panel_status_id']],
            ['table' => 'fuel_tanks', 'columns' => ['location_id', 'condition_id', 'material_id', 'usage_id', 'measurement_method_id']],
            ['table' => 'fuel_efficiencies', 'columns' => ['fuel_efficiency_comparison_id', 'energy_efficiency_comparison_id']],
            ['table' => 'maintenance_records', 'columns' => ['maintenance_type_id', 'next_maintenance_type_id']],
            ['table' => 'compliance_safeties', 'columns' => ['safety_certificate_status_id']],
            ['table' => 'inspection_violation_cases', 'columns' => ['governorate_id']],
        ];

        $usedIn = [];
        foreach ($tables as $item) {
            foreach ($item['columns'] as $column) {
                if (DB::table($item['table'])->where($column, $constantDetail->id)->exists()) {
                    $usedIn[] = $item['table'];
                    break;
                }
            }
        }

        // التحقق من وجود تفاصيل فرعية
        if ($constantDetail->children()->count() > 0) {
            $usedIn[] = 'تفاصيل فرعية';
        }

        if (!empty($usedIn)) {
            $message = 'لا يمكن حذف هذا الثابت لأنه مستخدم في سجلات النظام.';

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            return redirect()->back()->with('error', $message);
        }

        $constantDetail->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف التفصيل بنجاح.',
            ]);
        }

        return redirect()->back()
            ->with('success', 'تم حذف التفصيل بنجاح.');
    }

    /**
     * Get details by constant master ID (AJAX)
     */
    public function getByMaster(ConstantMaster $constantMaster): JsonResponse
    {
        $details = $constantMaster->details;

        return response()->json([
            'success' => true,
            'data' => $details->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'label' => $detail->label,
                    'code' => $detail->code,
                    'value' => $detail->value,
                ];
            }),
        ]);
    }

    /**
     * Get cities by governorate (AJAX)
     * يمكن تمرير governorate_id أو governorate_code
     */
    public function getCitiesByGovernorate(Request $request): JsonResponse
    {
        $governorateId = $request->input('governorate_id');
        $governorateCode = $request->input('governorate_code');

        if (!$governorateId && !$governorateCode) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تحديد المحافظة',
            ], 400);
        }

        $governoratesMaster = ConstantMaster::findByNumber(1);
        if (!$governoratesMaster) {
            return response()->json([
                'success' => false,
                'message' => 'ثابت المحافظات غير موجود',
            ], 404);
        }

        // البحث عن المحافظة
        $governorate = null;
        if ($governorateId) {
            $governorate = ConstantDetail::where('constant_master_id', $governoratesMaster->id)
                ->where('id', $governorateId)
                ->where('is_active', true)
                ->first();
        } elseif ($governorateCode) {
            $governorate = ConstantDetail::where('constant_master_id', $governoratesMaster->id)
                ->where('code', $governorateCode)
                ->where('is_active', true)
                ->first();
        }

        if (!$governorate) {
            return response()->json([
                'success' => false,
                'message' => 'المحافظة غير موجودة',
            ], 404);
        }

        // الحصول على المدن
        $cities = ConstantsHelper::getCitiesByGovernorate($governorate->id);

        return response()->json([
            'success' => true,
            'data' => $cities->map(function ($city) {
                return [
                    'id' => $city->id,
                    'label' => $city->label,
                    'code' => $city->code,
                    'value' => $city->value,
                ];
            }),
        ]);
    }
}
