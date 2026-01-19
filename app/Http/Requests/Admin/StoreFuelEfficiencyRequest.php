<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFuelEfficiencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin() || $this->user()->isCompanyOwner() || $this->user()->isEmployee();
    }

    public function rules(): array
    {
        return [
            'operator_id' => ['required', 'exists:operators,id'],
            'generation_unit_id' => ['required', 'exists:generation_units,id'],
            'generator_id' => ['required', 'exists:generators,id'],
            'consumption_date' => ['required', 'date'],
            'operating_hours' => ['nullable', 'numeric', 'min:0'],
            'fuel_price_per_liter' => ['nullable', 'numeric', 'min:0'],
            'fuel_consumed' => ['nullable', 'numeric', 'min:0'],
            'fuel_efficiency_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fuel_efficiency_comparison_id' => ['nullable', 'exists:constant_details,id'],
            'energy_distribution_efficiency' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'energy_efficiency_comparison_id' => ['nullable', 'exists:constant_details,id'],
            'total_operating_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'operator_id.required' => 'المشغل مطلوب.',
            'operator_id.exists' => 'المشغل المحدد غير موجود.',
            'generation_unit_id.required' => 'وحدة التوليد مطلوبة.',
            'generation_unit_id.exists' => 'وحدة التوليد المحددة غير موجودة.',
            'generator_id.required' => 'المولد مطلوب.',
            'generator_id.exists' => 'المولد المحدد غير موجود.',
            'consumption_date.required' => 'تاريخ الاستهلاك مطلوب.',
            'fuel_efficiency_comparison_id.exists' => 'مقارنة كفاءة الوقود غير صحيحة.',
            'energy_efficiency_comparison_id.exists' => 'مقارنة كفاءة الطاقة غير صحيحة.',
        ];
    }
}
