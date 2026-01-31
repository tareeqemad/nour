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
            'subscription_number' => ['required', 'string', 'max:255', 'unique:subscribers,subscription_number'],
            'subscriber_id_number' => ['required', 'string', 'max:255'],
            'subscriber_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'subscription_date' => ['required', 'date'],
            'subscription_category' => ['required', 'integer', 'in:1,2,3,4'],
            'phase_type' => ['required', 'integer', 'in:1,2'],
            'subscription_status' => ['required', 'integer', 'in:1,2,3'],
            'meter_number' => ['nullable', 'string', 'max:255'],
            'service_type' => ['required', 'integer', 'in:1,2,3'],
            'generation_unit_ids' => ['required', 'array', 'min:1'],
            'generation_unit_ids.*' => ['exists:generation_units,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subscription_number.required' => 'رقم الاشتراك مطلوب.',
            'subscription_number.unique' => 'رقم الاشتراك موجود مسبقاً.',
            'subscriber_id_number.required' => 'رقم هوية المشترك مطلوب.',
            'subscriber_name.required' => 'اسم المشترك مطلوب.',
            'phone.required' => 'رقم الجوال مطلوب.',
            'address.required' => 'عنوان المشترك مطلوب.',
            'subscription_date.required' => 'تاريخ الاشتراك مطلوب.',
            'subscription_category.required' => 'تصنيف الاشتراك مطلوب.',
            'subscription_category.in' => 'تصنيف الاشتراك غير صحيح.',
            'phase_type.required' => 'نوع الفاز مطلوب.',
            'phase_type.in' => 'نوع الفاز غير صحيح.',
            'subscription_status.required' => 'حالة الاشتراك مطلوبة.',
            'subscription_status.in' => 'حالة الاشتراك غير صحيحة.',
            'service_type.required' => 'نوع الخدمة مطلوب.',
            'service_type.in' => 'نوع الخدمة غير صحيح.',
            'generation_unit_ids.required' => 'يجب اختيار وحدة توليد واحدة على الأقل.',
            'generation_unit_ids.array' => 'وحدات التوليد يجب أن تكون مصفوفة.',
            'generation_unit_ids.min' => 'يجب اختيار وحدة توليد واحدة على الأقل.',
            'generation_unit_ids.*.exists' => 'إحدى وحدات التوليد المحددة غير موجودة.',
        ];
    }
}
