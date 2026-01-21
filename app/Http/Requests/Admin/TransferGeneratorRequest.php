<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferGeneratorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $generator = $this->route('generator');
        
        // Check permission using policy
        return $user->can('transfer', $generator);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'target_operator_id' => [
                'required',
                'exists:operators,id',
                Rule::notIn([$this->route('generator')->operator_id], 'The target operator must be different from the current operator'),
            ],
            'target_generation_unit_id' => [
                'required',
                'exists:generation_units,id',
                function ($attribute, $value, $fail) {
                    $targetOperatorId = $this->input('target_operator_id');
                    $generationUnit = \App\Models\GenerationUnit::find($value);
                    
                    if ($generationUnit && $generationUnit->operator_id != $targetOperatorId) {
                        $fail('وحدة التوليد المحددة يجب أن تكون تابعة للمشغل الهدف.');
                    }
                },
            ],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'target_operator_id.required' => 'يجب اختيار المشغل الهدف',
            'target_operator_id.exists' => 'المشغل المحدد غير موجود',
            'target_operator_id.not_in' => 'يجب اختيار مشغل مختلف عن المشغل الحالي',
            'target_generation_unit_id.required' => 'يجب اختيار وحدة التوليد الهدف',
            'target_generation_unit_id.exists' => 'وحدة التوليد المحددة غير موجودة',
            'reason.max' => 'سبب النقل يجب ألا يتجاوز 500 حرف',
        ];
    }
}
