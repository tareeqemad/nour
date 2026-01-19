<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin() || $this->user()->isCompanyOwner() || $this->user()->isEmployee();
    }

    public function rules(): array
    {
        return [
            'generator_id' => ['required', 'exists:generators,id'],
            'operator_id' => ['required', 'exists:operators,id'],
            'generation_unit_id' => ['required', 'exists:generation_units,id'],
            'operation_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'load_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fuel_meter_start' => ['nullable', 'numeric', 'min:0'],
            'fuel_meter_end' => ['nullable', 'numeric', 'min:0'],
            'fuel_consumed' => ['nullable', 'numeric', 'min:0'],
            'energy_meter_start' => ['nullable', 'numeric', 'min:0'],
            'energy_meter_end' => ['nullable', 'numeric', 'min:0'],
            'energy_produced' => ['nullable', 'numeric', 'min:0'],
            'electricity_tariff_price' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'operational_notes' => ['nullable', 'string'],
            'malfunctions' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'generator_id.required' => 'المولد مطلوب.',
            'generator_id.exists' => 'المولد المحدد غير موجود.',
            'operator_id.required' => 'المشغل مطلوب.',
            'operator_id.exists' => 'المشغل المحدد غير موجود.',
            'generation_unit_id.required' => 'وحدة التوليد مطلوبة.',
            'generation_unit_id.exists' => 'وحدة التوليد المحددة غير موجودة.',
            'operation_date.required' => 'تاريخ التشغيل مطلوب.',
            'start_time.required' => 'وقت البدء مطلوب.',
            'end_time.required' => 'وقت الإيقاف مطلوب.',
            'end_time.after' => 'وقت الإيقاف يجب أن يكون بعد وقت البدء.',
            'load_percentage.max' => 'نسبة التحميل يجب أن تكون بين 0 و 100.',
        ];
    }
}
