<?php

namespace App\Http\Requests;

use App\Models\AuthorizedPhone;
use App\Models\User;
use App\Models\Operator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreJoinRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // أي شخص يمكنه تقديم طلب انضمام
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // بيانات المالك - إلزامية
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_name_en' => ['required', 'string', 'max:255'],
            'owner_id_number' => ['required', 'string', 'regex:/^\d{9}$/'],
            // بيانات المشغل
            'operator_name' => ['required', 'string', 'max:255'],
            'operator_name_en' => ['nullable', 'string', 'max:255'],
            'operator_id_number' => ['nullable', 'string', 'regex:/^\d{9}$/'],
            // بيانات الاتصال
            'phone' => ['required', 'string', 'regex:/^0(59|56)\d{7}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'data_accuracy' => ['required', 'accepted'],
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
            'owner_name.required' => 'اسم المالك بالعربية مطلوب',
            'owner_name.string' => 'اسم المالك بالعربية يجب أن يكون نص',
            'owner_name.max' => 'اسم المالك بالعربية يجب أن لا يتجاوز 255 حرف',
            'owner_name_en.required' => 'اسم المالك بالإنجليزية مطلوب',
            'owner_name_en.string' => 'اسم المالك بالإنجليزية يجب أن يكون نص',
            'owner_name_en.max' => 'اسم المالك بالإنجليزية يجب أن لا يتجاوز 255 حرف',
            'owner_id_number.required' => 'رقم هوية المالك مطلوب',
            'owner_id_number.regex' => 'رقم هوية المالك يجب أن يكون 9 أرقام',
            'operator_name.required' => 'اسم المشغل بالعربية مطلوب',
            'operator_name.string' => 'اسم المشغل بالعربية يجب أن يكون نص',
            'operator_name.max' => 'اسم المشغل بالعربية يجب أن لا يتجاوز 255 حرف',
            'operator_name_en.string' => 'اسم المشغل بالإنجليزية يجب أن يكون نص',
            'operator_name_en.max' => 'اسم المشغل بالإنجليزية يجب أن لا يتجاوز 255 حرف',
            'operator_id_number.regex' => 'رقم هوية المشغل يجب أن يكون 9 أرقام',
            'phone.required' => 'رقم الموبايل مطلوب',
            'phone.regex' => 'رقم الموبايل يجب أن يكون 10 أرقام ويبدأ بـ 059 أو 056',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.max' => 'البريد الإلكتروني يجب أن لا يتجاوز 255 حرف',
            'data_accuracy.required' => 'يجب الموافقة على الإقرار بصحة البيانات',
            'data_accuracy.accepted' => 'يجب الموافقة على الإقرار بصحة البيانات',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $phone = $this->input('phone');
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            // التحقق من أن الرقم مصرح به
            if (!AuthorizedPhone::isAuthorized($cleanPhone)) {
                $validator->errors()->add('phone', 'غير مخول لك بالتسجيل. يرجى التواصل مع الإدارة.');
                return;
            }

            // التحقق من أن الرقم غير مسجل مسبقاً
            $existingUser = User::where('phone', $cleanPhone)
                ->orWhere('phone', $phone)
                ->first();

            if ($existingUser) {
                if ($existingUser->isSuperAdmin() || $existingUser->isAdmin() || $existingUser->isEnergyAuthority()) {
                    $validator->errors()->add('phone', 'هذا الرقم مسجل لحساب إداري. لا يمكن استخدام طلب الانضمام.');
                    return;
                }

                $existingOperator = Operator::where('owner_id', $existingUser->id)->first();
                if ($existingOperator) {
                    $validator->errors()->add('phone', 'مسجل مسبقاً. غير مسموح لك التسجيل مرة أخرى.');
                    return;
                }

                $validator->errors()->add('phone', 'هذا الرقم مسجل مسبقاً في النظام. لا يمكن استخدام طلب الانضمام.');
                return;
            }

            // التحقق من أن الرقم غير مسجل في جدول operators
            $existingOperatorByPhone = Operator::whereHas('owner', function($query) use ($cleanPhone, $phone) {
                $query->where('phone', $cleanPhone)
                      ->orWhere('phone', $phone);
            })->first();

            if ($existingOperatorByPhone) {
                $validator->errors()->add('phone', 'رقم الجوال مسجل مسبقاً. غير مسموح لك التسجيل مرة أخرى.');
            }
        });
    }
}
