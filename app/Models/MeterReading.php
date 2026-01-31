<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class MeterReading extends Model
{
    use HasFactory, SoftDeletes, TracksUser;

    protected $table = 'meter_readings';

    protected $fillable = [
        'reading_number',
        'subscriber_id',
        'meter_number',
        'previous_reading',
        'current_reading',
        'consumption_kwh',
        'reading_date',
        'consumption_period_days',
        'reading_status',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_reading' => 'decimal:2',
            'current_reading' => 'decimal:2',
            'consumption_kwh' => 'decimal:2',
            'reading_date' => 'date',
            'consumption_period_days' => 'integer',
            'reading_status' => 'integer',
        ];
    }

    /**
     * العلاقة مع المشترك
     */
    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ السجل
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المستخدم الذي حدث السجل
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * Accessor للحصول على اسم حالة القراءة
     */
    public function getReadingStatusNameAttribute(): string
    {
        return match($this->reading_status) {
            1 => 'طبيعية',
            2 => 'تقديرية',
            default => 'غير محدد',
        };
    }

    /**
     * الحصول على آخر قراءة للمشترك
     */
    public static function getLastReadingForSubscriber(int $subscriberId): ?self
    {
        return static::where('subscriber_id', $subscriberId)
            ->orderBy('reading_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * توليد رقم القراءة التالي
     */
    public static function generateReadingNumber(int $subscriberId): string
    {
        $subscriber = Subscriber::find($subscriberId);
        if (!$subscriber) {
            return 'READ-' . date('Ymd') . '-001';
        }

        // استخدام رقم الاشتراك + التاريخ + رقم تسلسلي
        $date = date('Ymd');
        $prefix = $subscriber->subscription_number . '-' . $date . '-';
        
        $lastReading = static::where('reading_number', 'like', $prefix . '%')
            ->get()
            ->map(function ($reading) use ($prefix) {
                $code = $reading->reading_number;
                if (str_starts_with($code, $prefix)) {
                    $numberPart = substr($code, strlen($prefix));
                    return (int) $numberPart;
                }
                return 0;
            })
            ->filter(fn($num) => $num > 0)
            ->sortDesc()
            ->first();

        if ($lastReading) {
            $nextNumber = $lastReading + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
