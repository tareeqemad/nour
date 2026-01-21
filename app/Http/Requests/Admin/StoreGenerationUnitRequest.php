<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGenerationUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\GenerationUnit::class);
    }

    public function rules(): array
    {
        // فقط CompanyOwner يمكنه إضافة وحدات التوليد - operator_id مطلوب دائماً
        return [
            'operator_id' => ['required', 'exists:operators,id'],
            
            // الحقول الأساسية المطلوبة فقط
            'name' => ['required', 'string', 'max:255'],
            'governorate_id' => ['required', 'exists:constant_details,id'],
            'city_id' => ['required', 'exists:constant_details,id'],
            'detailed_address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'territory_area_km2' => ['required', 'numeric', 'min:0.1', 'max:360'],
            'territory_radius_km' => ['nullable', 'numeric'], // سيتم حسابه تلقائياً من المساحة
            'territory_name' => ['required', 'string', 'max:255'],

            // باقي الحقول اختيارية (يمكن ملؤها لاحقاً)
            'generators_count' => ['nullable', 'integer', 'min:1', 'max:99'],
            'status_id' => ['nullable', 'exists:constant_details,id'],

            // الملكية والتشغيل
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_id_number' => ['nullable', 'string', 'max:255'],
            'operation_entity_id' => ['required', 'exists:constant_details,id'],
            'operator_id_number' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $operationEntityId = $this->input('operation_entity_id');
                    if ($operationEntityId) {
                        $operationEntity = \App\Models\ConstantDetail::find($operationEntityId);
                        if ($operationEntity && $operationEntity->code === 'OTHER_PARTY') {
                            // إذا كان "طرف آخر"، يجب إدخال رقم هوية المشغل
                            if (empty($value)) {
                                $fail('رقم هوية المشغل مطلوب عندما تكون جهة التشغيل "طرف آخر".');
                            } elseif (!preg_match('/^[0-9]{9}$/', $value)) {
                                $fail('رقم هوية المشغل يجب أن يتكون من 9 أرقام فقط.');
                            }
                        }
                    }
                },
            ],
            'operator_name' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $operationEntityId = $this->input('operation_entity_id');
                    if ($operationEntityId) {
                        $operationEntity = \App\Models\ConstantDetail::find($operationEntityId);
                        if ($operationEntity && $operationEntity->code === 'OTHER_PARTY') {
                            // إذا كان "طرف آخر"، يجب إدخال اسم المشغل
                            if (empty($value)) {
                                $fail('اسم المشغل مطلوب عندما تكون جهة التشغيل "طرف آخر".');
                            }
                        }
                    }
                },
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'phone_alt' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],

            // القدرات الفنية
            'total_capacity' => ['required', 'numeric', 'min:0.01'],
            'synchronization_available_id' => ['nullable', 'exists:constant_details,id'],
            'max_synchronization_capacity' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $syncAvailableId = $this->input('synchronization_available_id');
                    if ($syncAvailableId) {
                        $syncDetail = \App\Models\ConstantDetail::find($syncAvailableId);
                        if ($syncDetail && $syncDetail->code === 'AVAILABLE') {
                            // إذا كانت المزامنة متوفرة، يجب أن تكون القدرة أكبر من صفر (إجباري)
                            if ($value === null || $value === '' || (is_numeric($value) && $value <= 0)) {
                                $fail('يجب إدخال قدرة المزامنة القصوى أكبر من صفر عندما تكون المزامنة متوفرة.');
                            }
                        } else {
                            // إذا كانت غير متوفرة، يجب أن تكون صفر
                            if ($value !== null && $value !== '' && is_numeric($value) && $value != 0) {
                                $fail('يجب أن تكون قدرة المزامنة القصوى صفر عندما تكون المزامنة غير متوفرة.');
                            }
                        }
                    }
                },
            ],

            // المستفيدون والبيئة
            'beneficiaries_count' => ['nullable', 'integer', 'min:0'],
            'beneficiaries_description' => ['nullable', 'string'],
            'environmental_compliance_status_id' => ['nullable', 'exists:constant_details,id'],
            
            // خزانات الوقود
            'external_fuel_tank' => ['nullable', 'boolean'],
            'fuel_tanks_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'fuel_tanks' => ['nullable', 'array'],
            'fuel_tanks.*.capacity' => ['required_with:fuel_tanks', 'numeric', 'min:0', 'max:10000'],
            'fuel_tanks.*.location_id' => ['required_with:fuel_tanks', 'exists:constant_details,id'],
            'fuel_tanks.*.filtration_system_available' => ['nullable', 'boolean'],
            'fuel_tanks.*.condition_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_tanks.*.material_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_tanks.*.usage_id' => ['nullable', 'exists:constant_details,id'],
            'fuel_tanks.*.measurement_method_id' => ['nullable', 'exists:constant_details,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'operator_id.required' => 'يجب اختيار المشغل.',
            'operator_id.exists' => 'المشغل المحدد غير موجود.',
            'name.required' => 'اسم وحدة التوليد مطلوب.',
            'governorate_id.required' => 'المحافظة مطلوبة.',
            'governorate_id.exists' => 'المحافظة المحددة غير صحيحة.',
            'city_id.required' => 'المدينة مطلوبة.',
            'city_id.exists' => 'المدينة المحددة غير صحيحة.',
            'detailed_address.required' => 'العنوان التفصيلي مطلوب.',
            'latitude.required' => 'خط العرض مطلوب.',
            'longitude.required' => 'خط الطول مطلوب.',
            'generators_count.min' => 'يجب أن يكون عدد المولدات على الأقل 1.',
            'generators_count.max' => 'يجب ألا يتجاوز عدد المولدات 99.',
            'status_id.exists' => 'حالة الوحدة المحددة غير صحيحة.',
            'operation_entity_id.required' => 'جهة التشغيل مطلوبة.',
            'operation_entity_id.exists' => 'جهة التشغيل المحددة غير صحيحة.',
        ];
    }
}

