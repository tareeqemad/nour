<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('subscriber'));
    }

    public function rules(): array
    {
        $subscriberId = $this->route('subscriber')->id ?? null;
        $user = $this->user();
        
        $rules = [
            'phone' => ['required', 'string', 'regex:/^05[69]\\d{7}$/', 'unique:subscribers,phone,' . $subscriberId],
            'governorate_name' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'subscription_category' => ['required', 'integer', 'in:1,2,3,4'],
            'phase_type' => ['required', 'integer', 'in:1,2'],
            'subscription_status' => ['required', 'integer', 'in:1,2,3'],
            'meter_number' => ['required', 'string', 'max:255', 'unique:subscribers,meter_number,' . $subscriberId],
            'service_type' => ['required', 'integer', 'in:1,2,3'],
            'generation_unit_ids' => ['required', 'array', 'min:1'],
            'generation_unit_ids.*' => ['exists:generation_units,id'],
        ];
        
        // فقط SuperAdmin يمكنه تعديل رقم الاشتراك
        if ($user->isSuperAdmin()) {
            $rules['subscription_number'] = [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('subscribers', 'subscription_number')->ignore($subscriberId)
            ];
        }
        
        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'phone.required' => 'رقم الموبايل مطلوب.',
            'phone.regex' => 'رقم الموبايل يجب أن يكون 10 أرقام ويبدأ بـ 059 أو 056.',
            'phone.unique' => 'رقم الموبايل مسجل مسبقاً، يرجى استخدام رقم موبايل مختلف.',
            'address.required' => 'عنوان المشترك مطلوب.',
            'subscription_category.required' => 'تصنيف الاشتراك مطلوب.',
            'subscription_category.in' => 'تصنيف الاشتراك غير صحيح.',
            'phase_type.required' => 'نوع الفاز مطلوب.',
            'phase_type.in' => 'نوع الفاز غير صحيح.',
            'subscription_status.required' => 'حالة الاشتراك مطلوبة.',
            'subscription_status.in' => 'حالة الاشتراك غير صحيحة.',
            'meter_number.required' => 'رقم العداد مطلوب.',
            'meter_number.unique' => 'رقم العداد مسجل مسبقاً، يرجى استخدام رقم عداد مختلف.',
            'service_type.required' => 'نوع الخدمة مطلوب.',
            'service_type.in' => 'نوع الخدمة غير صحيح.',
            'generation_unit_ids.required' => 'يجب اختيار وحدة توليد واحدة على الأقل.',
            'generation_unit_ids.array' => 'وحدات التوليد يجب أن تكون مصفوفة.',
            'generation_unit_ids.min' => 'يجب اختيار وحدة توليد واحدة على الأقل.',
            'generation_unit_ids.*.exists' => 'إحدى وحدات التوليد المحددة غير موجودة.',
        ];
        
        // رسائل خاصة برقم الاشتراك (فقط لـ SuperAdmin)
        if ($this->user()->isSuperAdmin()) {
            $messages['subscription_number.required'] = 'رقم الاشتراك مطلوب.';
            $messages['subscription_number.unique'] = 'رقم الاشتراك موجود مسبقاً.';
        }
        
        return $messages;
    }
}
