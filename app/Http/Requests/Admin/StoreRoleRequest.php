<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isCompanyOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
        
        // السوبر أدمن فقط يمكنه اختيار المشغل
        if (auth()->user()->isSuperAdmin()) {
            $rules['operator_id'] = ['nullable', 'exists:operators,id'];
        }
        
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الدور مطلوب.',
            'name.unique' => 'اسم الدور مستخدم بالفعل.',
            'label.required' => 'تسمية الدور مطلوبة.',
            'permissions.*.exists' => 'إحدى الصلاحيات المحددة غير موجودة.',
        ];
    }
}
