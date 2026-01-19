<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreConstantDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'constant_master_id' => ['required', 'exists:constant_masters,id'],
            'label' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
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
            'constant_master_id.required' => 'الثابت الرئيسي مطلوب.',
            'constant_master_id.exists' => 'الثابت الرئيسي غير موجود.',
            'label.required' => 'البيان مطلوب.',
        ];
    }
}
