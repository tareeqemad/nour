<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGeneratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        
        // SuperAdmin دائماً مسموح
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        // Company Owner يمكنه إضافة المولدات حتى لو لم يكن معتمد
        if ($user->isCompanyOwner()) {
            return $user->ownedOperators()->first() !== null;
        }
        
        // Technician يمكنه أيضاً (من خلال Policy)
        if ($user->isTechnician()) {
            return $user->can('create', \App\Models\Generator::class);
        }
        
        return false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'generator_number' => ['nullable', 'string', 'max:255', 'unique:generators'],
            // operator_id سيتم أخذه من المستخدم في الـ controller لضمان السلامة
            // 'operator_id' => ['nullable', 'exists:operators,id'],
            'generation_unit_id' => ['required', 'exists:generation_units,id'],
            'description' => ['nullable', 'string'],
            'status_id' => ['required', 'exists:constant_details,id'],
            // المواصفات الفنية
            'capacity_kva' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if (empty($value) || $value <= 0) {
                        return; // إذا كانت القيمة فارغة أو صفر، لا حاجة للتحقق
                    }
                    
                    $generationUnitId = $this->input('generation_unit_id');
                    if (!$generationUnitId) {
                        return;
                    }
                    
                    $generationUnit = \App\Models\GenerationUnit::find($generationUnitId);
                    if (!$generationUnit || !$generationUnit->total_capacity) {
                        return; // إذا لم تكن هناك إجمالي قدرة محددة، لا حاجة للتحقق
                    }
                    
                    // حساب مجموع قدرات المولدات الحالية
                    $currentTotalCapacity = $generationUnit->generators()->sum('capacity_kva') ?? 0;
                    // إضافة القدرة الجديدة
                    $newTotalCapacity = $currentTotalCapacity + $value;
                    
                    if ($newTotalCapacity > $generationUnit->total_capacity) {
                        $remaining = $generationUnit->total_capacity - $currentTotalCapacity;
                        $fail("مجموع قدرات المولدات ({$newTotalCapacity} KVA) يتجاوز إجمالي القدرة لوحدة التوليد ({$generationUnit->total_capacity} KVA). القدرة المتبقية المتاحة: {$remaining} KVA.");
                    }
                },
            ],
            'power_factor' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'voltage' => ['nullable', 'integer', 'min:0'],
            'frequency' => ['nullable', 'integer', 'min:0'],
            'engine_type_id' => ['nullable', 'exists:constant_details,id'],
            // التشغيل والوقود
            'manufacturing_year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'injection_system_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_consumption_rate' => ['nullable', 'numeric', 'min:0'],
            'ideal_fuel_efficiency' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'internal_tank_capacity' => ['nullable', 'integer', 'min:0'],
            'measurement_indicator_id' => ['nullable', 'exists:constant_details,id'],
            // الحالة الفنية والتوثيق
            'technical_condition_id' => ['nullable', 'exists:constant_details,id'],
            'last_major_maintenance_date' => ['nullable', 'date'],
            'engine_data_plate_image' => ['nullable', 'image', 'max:2048'],
            'generator_data_plate_image' => ['nullable', 'image', 'max:2048'],
            // نظام التحكم
            'control_panel_available' => ['nullable', 'boolean'],
            'control_panel_type_id' => ['nullable', 'exists:constant_details,id'],
            'control_panel_status_id' => ['nullable', 'exists:constant_details,id'],
            'control_panel_image' => ['nullable', 'image', 'max:2048'],
            'operating_hours' => ['nullable', 'integer', 'min:0'],
            // خزانات الوقود
            'external_fuel_tank' => ['nullable', 'boolean'],
            'fuel_tanks_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'fuel_tanks' => ['nullable', 'array'],
            'fuel_tanks.*.capacity' => ['required_with:fuel_tanks', 'integer', 'min:0', 'max:10000'],
            'fuel_tanks.*.location_id' => ['required_with:fuel_tanks', 'exists:constant_details,id'],
            'fuel_tanks.*.filtration_system_available' => ['nullable', 'boolean'],
            'fuel_tanks.*.condition' => ['nullable', 'string'],
            'fuel_tanks.*.material_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_tanks.*.usage_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_tanks.*.measurement_method_id' => ['nullable', 'exists:constant_details,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم المولد مطلوب.',
            'generator_number.unique' => 'رقم المولد هذا مستخدم بالفعل.',
            'operator_id.exists' => 'المشغل المحدد غير موجود.',
            'generation_unit_id.required' => 'وحدة التوليد مطلوبة.',
            'generation_unit_id.exists' => 'وحدة التوليد المحددة غير موجودة.',
            'status_id.required' => 'حالة المولد مطلوبة.',
            'status_id.exists' => 'حالة المولد المحددة غير صحيحة.',
            // رسائل خزانات الوقود
            'fuel_tanks.*.capacity.required_with' => 'سعة الخزان مطلوبة.',
            'fuel_tanks.*.capacity.integer' => 'سعة الخزان يجب أن تكون رقماً صحيحاً.',
            'fuel_tanks.*.capacity.min' => 'سعة الخزان يجب أن تكون أكبر من أو تساوي 0.',
            'fuel_tanks.*.capacity.max' => 'سعة الخزان يجب أن تكون أقل من أو تساوي 10000 لتر.',
            'fuel_tanks.*.location_id.required_with' => 'موقع الخزان مطلوب.',
            'fuel_tanks.*.location_id.exists' => 'موقع الخزان المحدد غير صحيح.',
            'fuel_tanks.*.material_id.exists' => 'مادة التصنيع المحددة غير صحيحة.',
            'fuel_tanks.*.usage_id.exists' => 'نوع الاستخدام المحدد غير صحيح.',
            'fuel_tanks.*.measurement_method_id.exists' => 'طريقة القياس المحددة غير صحيحة.',
        ];
    }
}
