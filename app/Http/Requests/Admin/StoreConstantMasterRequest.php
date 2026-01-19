<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreConstantMasterRequest extends FormRequest
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
            'constant_number' => ['required', 'integer', 'unique:constant_masters,constant_number'],
            'constant_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:0'],
            'details' => ['nullable', 'array'],
            'details.*.label' => ['required_with:details', 'string', 'max:255'],
            'details.*.code' => ['nullable', 'string', 'max:255'],
            'details.*.value' => ['nullable', 'string', 'max:255'],
            'details.*.notes' => ['nullable', 'string'],
            'details.*.is_active' => ['nullable', 'boolean'],
            'details.*.order' => ['nullable', 'integer', 'min:0'],
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
            'constant_number.required' => 'رقم الثابت مطلوب.',
            'constant_number.unique' => 'رقم الثابت مستخدم بالفعل.',
            'constant_name.required' => 'اسم الثابت مطلوب.',
            'details.array' => 'يجب أن تكون التفاصيل مصفوفة.',
            'details.*.label.required_with' => 'البيان مطلوب لكل تفصيل.',
            'details.*.label.max' => 'البيان يجب ألا يتجاوز 255 حرفاً.',
        ];
    }
}
