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
            'subscriber_id_number' => ['required', 'string', 'max:255', 'unique:subscribers,subscriber_id_number'],
            'subscriber_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^05[69]\d{7}$/', 'unique:subscribers,phone'],
            'governorate_name' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'subscription_date' => ['required', 'date', 'before_or_equal:today'],
            'subscription_category' => ['required', 'integer', 'in:1,2,3,4'],
            'phase_type' => ['required', 'integer', 'in:1,2'],
            'subscription_status' => ['required', 'integer', 'in:1,2,3'],
            'meter_number' => ['required', 'string', 'max:255', 'unique:subscribers,meter_number'],
            'service_type' => ['required', 'integer', 'in:1,2,3'],
            'generation_unit_ids' => ['required', 'array', 'min:1'],
            'generation_unit_ids.*' => ['exists:generation_units,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subscriber_id_number.required' => 'رقم هوية المشترك مطلوب.',
            'subscriber_id_number.unique' => 'رقم الهوية مسجل مسبقاً، يرجى استخدام رقم هوية مختلف.',
            'subscriber_name.required' => 'اسم المشترك مطلوب.',
            'phone.required' => 'رقم الموبايل مطلوب.',
            'phone.regex' => 'رقم الموبايل يجب أن يكون 10 أرقام ويبدأ بـ 059 أو 056.',
            'phone.unique' => 'رقم الموبايل مسجل مسبقاً، يرجى استخدام رقم موبايل مختلف.',
            'address.required' => 'عنوان المشترك مطلوب.',
            'subscription_date.required' => 'تاريخ الاشتراك مطلوب.',
            'subscription_date.before_or_equal' => 'تاريخ الاشتراك لا يمكن أن يكون في المستقبل.',
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
    }
}
