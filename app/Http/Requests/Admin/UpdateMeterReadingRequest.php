<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Subscriber;
use App\Models\MeterReading;

class UpdateMeterReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('meter_reading'));
    }

    public function rules(): array
    {
        $meterReading = $this->route('meter_reading');
        $subscriberId = $this->input('subscriber_id', $meterReading->subscriber_id ?? null);
        
        return [
            'subscriber_id' => ['required', 'exists:subscribers,id'],
            'meter_number' => ['required', 'string', 'max:255'],
            'previous_reading' => ['required', 'numeric', 'min:0'],
            'current_reading' => [
                'required', 
                'numeric', 
                'min:0',
                function ($attribute, $value, $fail) {
                    $previousReading = $this->input('previous_reading');
                    if ($previousReading !== null && $value < $previousReading) {
                        $fail('القراءة الحالية يجب أن تكون أكبر من أو تساوي القراءة السابقة.');
                    }
                },
            ],
            'consumption_kwh' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $previousReading = $this->input('previous_reading');
                    $currentReading = $this->input('current_reading');
                    if ($previousReading !== null && $currentReading !== null) {
                        $expectedConsumption = $currentReading - $previousReading;
                        if (abs($value - $expectedConsumption) > 0.01) {
                            $fail('قيمة الاستهلاك يجب أن تساوي الفرق بين القراءة الحالية والسابقة.');
                        }
                    }
                },
            ],
            'reading_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($subscriberId, $meterReading) {
                    if ($subscriberId) {
                        $subscriber = Subscriber::find($subscriberId);
                        if ($subscriber && $subscriber->subscription_status != 1) {
                            $fail('لا يمكن إدخال قراءة لاشتراك موقوف أو مغلق.');
                        }
                        
                        // التحقق من أن تاريخ القراءة لا يسبق آخر قراءة (باستثناء هذه القراءة)
                        $lastReading = MeterReading::where('subscriber_id', $subscriberId)
                            ->where('id', '!=', $meterReading->id)
                            ->orderBy('reading_date', 'desc')
                            ->orderBy('id', 'desc')
                            ->first();
                            
                        if ($lastReading && $value < $lastReading->reading_date->format('Y-m-d')) {
                            $fail('تاريخ القراءة يجب أن يكون بعد تاريخ آخر قراءة (' . $lastReading->reading_date->format('Y-m-d') . ').');
                        }
                    }
                },
            ],
            'consumption_period_days' => ['required', 'integer', 'min:1'],
            'reading_status' => ['required', 'integer', 'in:1,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'subscriber_id.required' => 'رقم الاشتراك مطلوب.',
            'subscriber_id.exists' => 'المشترك المحدد غير موجود.',
            'meter_number.required' => 'رقم العداد مطلوب.',
            'previous_reading.required' => 'القراءة السابقة مطلوبة.',
            'previous_reading.numeric' => 'القراءة السابقة يجب أن تكون رقماً.',
            'previous_reading.min' => 'القراءة السابقة يجب أن تكون أكبر من أو تساوي صفر.',
            'current_reading.required' => 'القراءة الحالية مطلوبة.',
            'current_reading.numeric' => 'القراءة الحالية يجب أن تكون رقماً.',
            'current_reading.min' => 'القراءة الحالية يجب أن تكون أكبر من أو تساوي صفر.',
            'consumption_kwh.required' => 'قيمة الاستهلاك مطلوبة.',
            'consumption_kwh.numeric' => 'قيمة الاستهلاك يجب أن تكون رقماً.',
            'consumption_kwh.min' => 'قيمة الاستهلاك يجب أن تكون أكبر من أو تساوي صفر.',
            'reading_date.required' => 'تاريخ القراءة مطلوب.',
            'reading_date.date' => 'تاريخ القراءة غير صحيح.',
            'consumption_period_days.required' => 'فترة الاستهلاك مطلوبة.',
            'consumption_period_days.integer' => 'فترة الاستهلاك يجب أن تكون رقماً صحيحاً.',
            'consumption_period_days.min' => 'فترة الاستهلاك يجب أن تكون على الأقل يوم واحد.',
            'reading_status.required' => 'حالة القراءة مطلوبة.',
            'reading_status.in' => 'حالة القراءة غير صحيحة.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $meterReading = $this->route('meter_reading');
        $subscriberId = $this->input('subscriber_id', $meterReading->subscriber_id ?? null);
        
        // حساب قيمة الاستهلاك تلقائياً إذا لم يتم إدخالها
        if ($this->has('previous_reading') && $this->has('current_reading')) {
            $previous = (float) $this->input('previous_reading');
            $current = (float) $this->input('current_reading');
            if ($current >= $previous) {
                $this->merge([
                    'consumption_kwh' => $current - $previous,
                ]);
            }
        }
        
        // حساب فترة الاستهلاك تلقائياً إذا لم يتم إدخالها
        if ($this->has('reading_date') && $subscriberId && !$this->has('consumption_period_days')) {
            $readingDate = $this->input('reading_date');
            $lastReading = MeterReading::where('subscriber_id', $subscriberId)
                ->where('id', '!=', $meterReading->id)
                ->orderBy('reading_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($lastReading && $lastReading->reading_date) {
                $lastDate = \Carbon\Carbon::parse($lastReading->reading_date);
                $currentDate = \Carbon\Carbon::parse($readingDate);
                $days = $lastDate->diffInDays($currentDate);
                $this->merge([
                    'consumption_period_days' => max(1, $days),
                ]);
            } else {
                // إذا لم توجد قراءة سابقة، استخدم 30 يوم كافتراضي
                $this->merge([
                    'consumption_period_days' => 30,
                ]);
            }
        }
    }
}
