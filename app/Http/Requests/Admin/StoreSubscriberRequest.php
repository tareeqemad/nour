<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Subscriber::class);
    }

    public function rules(): array
    {
        return [
            'subscriber_id_number' => ['required', 'string', 'max:255'],
            'subscriber_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^05\d{8}$/'],
            'alt_phone' => ['nullable', 'string', 'regex:/^05\d{8}$/'],
            'address' => ['required', 'string'],
            'subscription_date' => ['nullable', 'date', 'before_or_equal:today'],
            'request_date' => ['nullable', 'date', 'before_or_equal:today'],
            'subscription_category' => ['required', 'integer', 'in:1,2,3,4'],
            'phase_type' => ['required', 'integer', 'in:1,2'],
            'subscription_status' => ['required', 'integer', 'in:1,2,3'],
            'meter_number' => ['nullable', 'string', 'max:255'],
            'ampere' => ['nullable', 'numeric', 'min:0'],
            'opening_reading' => ['nullable', 'numeric', 'min:0'],
            'service_type' => ['required', 'integer', 'in:1,2,3'],
            'box_number' => ['nullable', 'string', 'regex:/^\d{1,4}$/'],
            'is_employee_subscription' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'operator_id' => ['nullable', 'exists:operators,id'],
            'generation_unit_id' => ['required', 'exists:generation_units,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subscriber_id_number.required' => 'رقم هوية المشترك مطلوب.',
            'subscriber_name.required' => 'اسم المشترك مطلوب.',
            'phone.required' => 'رقم الموبايل مطلوب.',
            'phone.regex' => 'رقم الموبايل يجب أن يكون 10 أرقام ويبدأ بـ 05.',
            'alt_phone.regex' => 'رقم الجوال البديل يجب أن يكون 10 أرقام ويبدأ بـ 05.',
            'address.required' => 'عنوان المشترك مطلوب.',
            'subscription_date.before_or_equal' => 'تاريخ الاشتراك لا يمكن أن يكون في المستقبل.',
            'request_date.before_or_equal' => 'تاريخ الطلب لا يمكن أن يكون في المستقبل.',
            'box_number.regex' => 'رقم الصندوق يجب أن يكون من 1 إلى 4 أرقام.',
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
            'generation_unit_id.required' => 'يجب اختيار وحدة توليد.',
            'generation_unit_id.exists' => 'وحدة التوليد المحددة غير موجودة.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_employee_subscription' => $this->has('is_employee_subscription') ? (bool) $this->is_employee_subscription : false,
        ]);
    }
}
