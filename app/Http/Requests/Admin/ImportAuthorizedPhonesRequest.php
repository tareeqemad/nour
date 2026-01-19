<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportAuthorizedPhonesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\AuthorizedPhone::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // 10MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'يجب اختيار ملف Excel للاستيراد',
            'file.file' => 'الملف المرفق غير صحيح',
            'file.mimes' => 'نوع الملف غير مدعوم. يجب أن يكون ملف Excel (.xlsx, .xls, .csv)',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
        ];
    }
}
