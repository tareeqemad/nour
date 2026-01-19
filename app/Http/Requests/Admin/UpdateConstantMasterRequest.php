<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConstantMasterRequest extends FormRequest
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
        $constant = $this->route('constant');

        return [
            'constant_number' => ['required', 'integer', \Illuminate\Validation\Rule::unique('constant_masters', 'constant_number')->ignore($constant->id)],
            'constant_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
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
            'constant_number.required' => 'رقم الثابت مطلوب.',
            'constant_number.unique' => 'رقم الثابت مستخدم بالفعل.',
            'constant_name.required' => 'اسم الثابت مطلوب.',
        ];
    }
}
