<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy will handle authorization
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = auth()->user();
        
        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'send_to' => ['required', 'in:user,operator,all_operators,my_staff'],
            'attachment' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'], // 10MB max
            'cc' => ['nullable', 'array'],
            'cc.*' => ['exists:users,id'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['exists:users,id'],
        ];

        // إذا كان send_to = user، يجب تحديد receiver_id
        if ($this->input('send_to') === 'user') {
            $rules['receiver_id'] = ['required', 'exists:users,id'];
        }

        // إذا كان send_to = operator، يجب تحديد operator_id
        if ($this->input('send_to') === 'operator') {
            $rules['operator_id'] = ['required', 'exists:operators,id'];
        }

        // all_operators و my_staff لا يحتاجون receiver_id أو operator_id

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'الموضوع مطلوب',
            'subject.max' => 'الموضوع يجب ألا يتجاوز 255 حرف',
            'body.required' => 'محتوى الرسالة مطلوب',
            'body.max' => 'محتوى الرسالة يجب ألا يتجاوز 5000 حرف',
            'send_to.required' => 'يجب تحديد نوع المرسل إليه',
            'receiver_id.required' => 'يجب تحديد المستخدم المستقبل',
            'receiver_id.exists' => 'المستخدم المحدد غير موجود',
            'operator_id.required' => 'يجب تحديد المشغل',
            'operator_id.exists' => 'المشغل المحدد غير موجود',
            'attachment.image' => 'الملف المرفق يجب أن يكون صورة.',
            'attachment.mimes' => 'نوع الصورة المدعوم: JPEG, JPG, PNG, GIF, WEBP',
            'attachment.max' => 'حجم الصورة يجب ألا يتجاوز 10 ميجابايت.',
        ];
    }
}
