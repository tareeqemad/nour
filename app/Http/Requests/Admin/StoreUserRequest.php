<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Enums\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();

        // ✅ خلّي القرار من الـ Policy (UserPolicy@create)
        return $actor ? $actor->can('create', User::class) : false;
    }

    protected function failedAuthorization()
    {
        // عشان الـ JS يطلع Toast مفهوم بدل رسالة Laravel الافتراضية
        throw new AuthorizationException('غير مصرح لك بإضافة مستخدم.');
    }

    public function rules(): array
    {
        $actor = $this->user();

        // roles المسموحة حسب نوع المستخدم
        $allowedRoles = array_map(fn (Role $r) => $r->value, Role::cases());

        if ($actor && $actor->isCompanyOwner()) {
            // ✅ المشغّل: يمكنه إضافة الأدوار المخصصة فقط (لا أدوار نظامية)
            $allowedRoles = [];
            
            // إضافة الأدوار المخصصة المتاحة للمشغل
            $customRoles = \App\Models\Role::getAvailableCustomRoles($actor);
            foreach ($customRoles as $customRole) {
                $allowedRoles[] = $customRole->name;
            }
        } elseif ($actor && $actor->isAdmin()) {
            // ✅ Admin: يمكنه إضافة Admin, EnergyAuthority, Technician, CivilDefense
            $allowedRoles = [
                Role::Admin->value,
                Role::EnergyAuthority->value,
                Role::Technician->value,
                Role::CivilDefense->value,
            ];
        } elseif ($actor && $actor->isEnergyAuthority()) {
            // ✅ EnergyAuthority: يمكنه إضافة EnergyAuthority, Technician, CivilDefense
            $allowedRoles = [
                Role::EnergyAuthority->value,
                Role::Technician->value,
                Role::CivilDefense->value,
            ];
        }

        $role = (string) $this->input('role');
        
        // تحديد ما إذا كان operator_id مطلوب (لـ CompanyOwner فقط)
        $needOperatorForCompanyOwner = $actor && $actor->isSuperAdmin()
            && $role === Role::CompanyOwner->value;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^0(59|56)\d{7}$/',
            ],
            'username' => [
                'nullable', // سيتم توليده تلقائياً
                'string',
                'max:50',
                Rule::unique('users', 'username')->whereNull('deleted_at'),
            ],
            'email' => [
                'nullable', // سيتم توليده تلقائياً
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'role' => ['required', Rule::in($allowedRoles)],
            'operator_id' => array_values(array_filter([
                $needOperatorForCompanyOwner ? 'required' : 'nullable',
                'integer',
                'exists:operators,id',
            ])),
            'password' => [
                'nullable', // سيتم توليده تلقائياً
                'string',
                'min:8',
                'confirmed'
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        // تنظيف بسيط (اختياري)
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'name_en' => is_string($this->name_en) ? trim($this->name_en) : $this->name_en,
            'phone' => is_string($this->phone) ? preg_replace('/[^0-9]/', '', trim($this->phone)) : $this->phone,
            'username' => is_string($this->username) ? trim($this->username) : $this->username,
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
        ]);
    }
}
