<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOperatorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isCompanyOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_id_number' => ['required', 'string', 'size:9', 'regex:/^[0-9]{9}$/'],
            'operator_id_number' => ['required', 'string', 'size:9', 'regex:/^[0-9]{9}$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المشغل مطلوب.',
            'name.max' => 'اسم المشغل يجب ألا يتجاوز 255 حرفاً.',
            'owner_name.required' => 'اسم المالك مطلوب.',
            'owner_name.max' => 'اسم المالك يجب ألا يتجاوز 255 حرفاً.',
            'owner_id_number.required' => 'رقم هوية المالك مطلوب.',
            'owner_id_number.size' => 'رقم هوية المالك يجب أن يكون 9 أرقام بالضبط.',
            'owner_id_number.regex' => 'رقم هوية المالك يجب أن يحتوي على أرقام فقط (9 أرقام).',
            'operator_id_number.required' => 'رقم هوية المشغل مطلوب.',
            'operator_id_number.size' => 'رقم هوية المشغل يجب أن يكون 9 أرقام بالضبط.',
            'operator_id_number.regex' => 'رقم هوية المشغل يجب أن يحتوي على أرقام فقط (9 أرقام).',
        ];
    }
}
