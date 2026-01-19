<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $operator = $this->route('operator');

        return auth()->user()->isSuperAdmin() || auth()->user()->ownsOperator($operator);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $operator = $this->route('operator');
        $userId = $operator?->owner_id;
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'user_email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
        ];

        // فقط SuperAdmin يمكنه تغيير username
        if ($isSuperAdmin) {
            $rules['username'] = ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)];
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
            'name.required' => 'اسم المشغل مطلوب.',
            'username.unique' => 'اسم المستخدم مستخدم بالفعل.',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'user_email.email' => 'البريد الإلكتروني غير صحيح.',
            'user_email.unique' => 'البريد الإلكتروني مستخدم بالفعل.',
        ];
    }
}
