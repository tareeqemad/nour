<?php

namespace App\Http\Requests;

use App\Governorate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComplaintSuggestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // أي شخص يمكنه إرسال شكوى/مقترح
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['complaint', 'suggestion'])],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'governorate' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!Governorate::tryFrom($value)) {
                        $fail('يرجى اختيار محافظة صحيحة');
                    }
                },
            ],
            'generator_id' => ['nullable', 'exists:generators,id'],
            'message' => ['required', 'string', 'min:10'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
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
            'type.required' => 'يرجى اختيار نوع الطلب',
            'type.in' => 'نوع الطلب يجب أن يكون شكوى أو مقترح',
            'name.required' => 'يرجى إدخال الاسم',
            'name.string' => 'الاسم يجب أن يكون نص',
            'name.max' => 'الاسم يجب أن لا يتجاوز 255 حرف',
            'phone.required' => 'يرجى إدخال رقم الهاتف',
            'phone.string' => 'رقم الهاتف يجب أن يكون نص',
            'phone.max' => 'رقم الهاتف يجب أن لا يتجاوز 20 حرف',
            'email.email' => 'البريد الإلكتروني غير صحيح',
            'email.max' => 'البريد الإلكتروني يجب أن لا يتجاوز 255 حرف',
            'governorate.required' => 'يرجى اختيار المحافظة',
            'governorate.integer' => 'يرجى اختيار محافظة صحيحة',
            'generator_id.exists' => 'يرجى اختيار مولد صحيح',
            'message.required' => 'يرجى إدخال الرسالة',
            'message.string' => 'الرسالة يجب أن تكون نص',
            'message.min' => 'الرسالة يجب أن تكون على الأقل 10 أحرف',
            'image.image' => 'الملف المرفوع يجب أن يكون صورة',
            'image.mimes' => 'نوع الصورة يجب أن يكون: jpeg, png, jpg, gif',
            'image.max' => 'حجم الصورة يجب أن لا يتجاوز 5 ميجابايت',
        ];
    }
}
