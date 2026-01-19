<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin() || $this->user()->isCompanyOwner() || $this->user()->isEmployee();
    }

    public function rules(): array
    {
        return [
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
        ];
    }

    public function messages(): array
    {
        return [
            'generator_id.required' => 'المولد مطلوب.',
            'generator_id.exists' => 'المولد المحدد غير موجود.',
            'maintenance_type_id.required' => 'نوع الصيانة مطلوب.',
            'maintenance_type_id.exists' => 'نوع الصيانة المحدد غير صحيح.',
            'next_maintenance_type_id.exists' => 'نوع الصيانة القادمة المحدد غير صحيح.',
            'maintenance_date.required' => 'تاريخ الصيانة مطلوب.',
        ];
    }
}
