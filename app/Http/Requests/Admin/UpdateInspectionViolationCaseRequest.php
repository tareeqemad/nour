<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInspectionViolationCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('inspection_violation_case'));
    }

    public function rules(): array
    {
        return [
            'governorate_id' => ['nullable', 'exists:constant_details,id'],
            'subscription_number' => ['nullable', 'string', 'max:100'],
            'subscriber_name' => ['required', 'string', 'max:255'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'case_date' => ['required', 'date'],
            'status' => ['required', 'integer', 'in:1,2,3'],
            'statement' => ['nullable', 'string'],
            'operator_id' => ['nullable', 'exists:operators,id'],
            'generation_unit_id' => ['nullable', 'exists:generation_units,id'],
        ];
    }
}
