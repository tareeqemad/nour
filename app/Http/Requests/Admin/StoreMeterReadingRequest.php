<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Subscriber;
use App\Models\MeterReading;

class StoreMeterReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\MeterReading::class);
    }

    public function rules(): array
    {
        $subscriberId = $this->input('subscriber_id');
        
        return [
            'subscriber_id' => ['required', 'exists:subscribers,id'],
            'meter_number' => ['nullable', 'string', 'max:255'],
            'previous_reading' => ['required', 'numeric', 'min:0'],
            'current_reading' => [
                'required', 
                'numeric', 
                'min:0',
            ],
            'consumption_kwh' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $previousReading = $this->input('previous_reading');
                    $currentReading = $this->input('current_reading');
                    if ($previousReading !== null && $currentReading !== null) {
                        $expectedConsumption = (float)$currentReading - (float)$previousReading;
                        if (abs($value - $expectedConsumption) > 0.01) {
                            $fail('قيمة الاستهلاك يجب أن تساوي الفرق بين القراءة الحالية والسابقة.');
                        }
                    }
                },
            ],
            'reading_date' => [
                'required',
                'date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) use ($subscriberId) {
                    if ($subscriberId) {
                        $subscriber = Subscriber::find($subscriberId);
                        if ($subscriber && $subscriber->subscription_status != 1) {
                            $fail('لا يمكن إدخال قراءة لاشتراك موقوف أو مغلق.');
                        }
                        
                        // التحقق من أن تاريخ القراءة لا يسبق آخر قراءة
                        $lastReading = MeterReading::getLastReadingForSubscriber($subscriberId);
                        if ($lastReading && $value < $lastReading->reading_date->format('Y-m-d')) {
                            $fail('تاريخ القراءة يجب أن يكون بعد تاريخ آخر قراءة (' . $lastReading->reading_date->format('Y-m-d') . ').');
                        }
                    }
                },
            ],
            'consumption_period_days' => ['required', 'integer', 'min:1'],
            // reading_status يتم تحديده تلقائياً في prepareForValidation
            'reading_status' => ['required', 'integer', 'in:1,2'],
            'previous_reading_date' => ['nullable', 'date'],
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
            'reading_date.before_or_equal' => 'تاريخ القراءة لا يمكن أن يكون في المستقبل.',
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
        $subscriberId = $this->input('subscriber_id');
        
        // حساب قيمة الاستهلاك تلقائياً (يمكن أن تكون سالبة أو صفر)
        if ($this->has('previous_reading') && $this->has('current_reading')) {
            $previous = (float) $this->input('previous_reading');
            $current = (float) $this->input('current_reading');
            $consumption = $current - $previous;
            $this->merge([
                'consumption_kwh' => $consumption,
            ]);
            // تحديد حالة القراءة تلقائياً: غير طبيعية إذا كان الاستهلاك <= 0
            $this->merge([
                'reading_status' => \App\Models\MeterReading::determineReadingStatus($consumption),
            ]);
        }
        
        // حساب فترة الاستهلاك تلقائياً دائماً
        if ($this->has('reading_date') && $subscriberId) {
            $readingDate = $this->input('reading_date');
            $lastReading = MeterReading::getLastReadingForSubscriber($subscriberId);
            $previousDate = null;

            if ($lastReading) {
                $previousDate = $lastReading->reading_date;
            } else {
                // First reading - use subscription_date
                $subscriber = \App\Models\Subscriber::find($subscriberId);
                $previousDate = $subscriber?->subscription_date;
            }

            if ($previousDate && $readingDate) {
                $days = \Carbon\Carbon::parse($previousDate)->diffInDays(\Carbon\Carbon::parse($readingDate)) + 1;
                $this->merge([
                    'consumption_period_days' => $days,
                    'previous_reading_date' => $previousDate instanceof \Carbon\Carbon ? $previousDate->format('Y-m-d') : $previousDate,
                ]);
            } else {
                $this->merge([
                    'consumption_period_days' => 1,
                ]);
            }
        }
    }
}
