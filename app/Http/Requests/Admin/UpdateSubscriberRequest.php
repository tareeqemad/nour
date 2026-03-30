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
            'phone' => ['required', 'string', 'regex:/^05\d{8}$/'],
            'alt_phone' => ['nullable', 'string', 'regex:/^05\d{8}$/'],
            'address' => ['required', 'string'],
            'request_date' => ['nullable', 'date', 'before_or_equal:today'],
            'subscription_category' => ['required', 'integer', 'in:1,2,3,4'],
            'phase_type' => ['required', 'integer', 'in:1,2'],
            'subscription_status' => ['required', 'integer', 'in:1,2,3'],
            'meter_number' => ['nullable', 'string', 'max:255'],
            'ampere' => ['nullable', 'numeric', 'min:0'],
            'opening_reading' => ['nullable', 'numeric', 'min:0'],
            'service_type' => ['required', 'integer', 'in:1,2,3'],
            'box_number' => ['nullable', 'string', 'regex:/^\d{4}$/'],
            'is_employee_subscription' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'phone.regex' => 'رقم الموبايل يجب أن يكون 10 أرقام ويبدأ بـ 05.',
            'alt_phone.regex' => 'رقم الجوال البديل يجب أن يكون 10 أرقام ويبدأ بـ 05.',
            'address.required' => 'عنوان المشترك مطلوب.',
            'request_date.before_or_equal' => 'تاريخ الطلب لا يمكن أن يكون في المستقبل.',
            'box_number.regex' => 'رقم الصندوق يجب أن يكون 4 أرقام.',
            'notes.max' => 'الملاحظات يجب أن لا تتجاوز 1000 حرف.',
            'subscription_category.required' => 'تصنيف الاشتراك مطلوب.',
            'subscription_category.in' => 'تصنيف الاشتراك غير صحيح.',
            'phase_type.required' => 'نوع الفاز مطلوب.',
            'phase_type.in' => 'نوع الفاز غير صحيح.',
            'subscription_status.required' => 'حالة الاشتراك مطلوبة.',
            'subscription_status.in' => 'حالة الاشتراك غير صحيحة.',
            'ampere.numeric' => 'قيمة الأمبير يجب أن تكون رقماً.',
            'ampere.min' => 'قيمة الأمبير يجب أن تكون أكبر من أو تساوي صفر.',
            'opening_reading.numeric' => 'قراءة العداد الافتتاحية يجب أن تكون رقماً.',
            'opening_reading.min' => 'قراءة العداد الافتتاحية يجب أن تكون أكبر من أو تساوي صفر.',
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'meter_number' => $this->meter_number !== '' ? $this->meter_number : null,
        ]);
    }
}
