<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Announcement::class);
    }

    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string'],
            'announcement_date' => ['required', 'date'],
            'start_date'        => ['required', 'date'],
            'end_date'          => ['required', 'date', 'after_or_equal:start_date'],
            'is_featured'       => ['sometimes', 'boolean'],
            'is_visible'        => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'             => 'عنوان الإعلان مطلوب.',
            'description.required'       => 'نص الإعلان مطلوب.',
            'announcement_date.required' => 'تاريخ الإعلان مطلوب.',
            'start_date.required'        => 'تاريخ البداية مطلوب.',
            'end_date.required'          => 'تاريخ النهاية مطلوب.',
            'end_date.after_or_equal'    => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_visible'  => $this->has('is_visible') ? $this->boolean('is_visible') : true,
        ]);
    }
}
