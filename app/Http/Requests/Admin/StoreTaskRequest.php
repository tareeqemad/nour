<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['maintenance', 'safety_inspection'])],
            'assigned_to' => ['required', 'exists:users,id'],
            'operator_id' => ['required', 'exists:operators,id'],
            'generation_unit_id' => ['nullable', 'exists:generation_units,id'],
            'generator_id' => ['nullable', 'exists:generators,id'],
            'description' => ['required', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'type.required' => 'نوع المهمة مطلوب',
            'type.in' => 'نوع المهمة يجب أن يكون صيانة أو فحص سلامة',
            'assigned_to.required' => 'يجب اختيار المستخدم المكلف',
            'assigned_to.exists' => 'المستخدم المكلف غير موجود',
            'operator_id.required' => 'يجب اختيار المشغل',
            'operator_id.exists' => 'المشغل غير موجود',
            'generation_unit_id.exists' => 'وحدة التوليد غير موجودة',
            'generator_id.exists' => 'المولد غير موجود',
            'description.required' => 'وصف المهمة مطلوب',
            'description.string' => 'وصف المهمة يجب أن يكون نص',
            'description.max' => 'وصف المهمة يجب أن لا يتجاوز 1000 حرف',
            'due_date.date' => 'تاريخ الاستحقاق غير صحيح',
            'due_date.after' => 'تاريخ الاستحقاق يجب أن يكون في المستقبل',
            'notes.string' => 'الملاحظات يجب أن تكون نص',
            'notes.max' => 'الملاحظات يجب أن لا تتجاوز 1000 حرف',
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
            $assignedUserId = $this->input('assigned_to');
            $type = $this->input('type');

            if ($assignedUserId) {
                $assignedUser = User::find($assignedUserId);

                if (!$assignedUser) {
                    return;
                }

                // التحقق من أن المستخدم المكلف هو فني أو دفاع مدني
                if (!$assignedUser->isTechnician() && !$assignedUser->isCivilDefense()) {
                    $validator->errors()->add('assigned_to', 'يجب اختيار فني أو دفاع مدني');
                    return;
                }

                // التحقق من أن نوع المهمة يطابق دور المستخدم
                if ($type === 'maintenance' && !$assignedUser->isTechnician()) {
                    $validator->errors()->add('type', 'مهمة الصيانة يجب أن تُكلف لفني');
                }

                if ($type === 'safety_inspection' && !$assignedUser->isCivilDefense()) {
                    $validator->errors()->add('type', 'مهمة فحص السلامة يجب أن تُكلف لدفاع مدني');
                }
            }
        });
    }
}
