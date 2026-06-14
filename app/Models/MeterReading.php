<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;
use App\Models\Setting;
use App\Helpers\ConstantsHelper;

use Illuminate\Database\Eloquent\Relations\HasOne;

class MeterReading extends Model
{
    use HasFactory, SoftDeletes, TracksUser;

    protected $table = 'meter_readings';

    // ثوابت حالة الإجراء
    const ACTION_STATUS_PENDING  = 0; // غير معتمدة
    const ACTION_STATUS_APPROVED = 1; // معتمدة
    const ACTION_STATUS_BILLED   = 2; // مفوترة

    // ثوابت حالة القراءة
    const READING_STATUS_NORMAL   = 1; // طبيعية
    const READING_STATUS_ABNORMAL = 2; // غير طبيعية

    protected $fillable = [
        'reading_number',
        'subscriber_id',
        'meter_number',
        'previous_reading',
        'current_reading',
        'consumption_kwh',
        'reading_date',
        'previous_reading_date',
        'consumption_period_days',
        'reading_status',
        'action_status',
        'abnormal_reason',
        'approved_by',
        'approved_at',
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
            'previous_reading_date' => 'date',
            'consumption_period_days' => 'integer',
            'reading_status' => 'integer',
            'action_status' => 'integer',
            'approved_at' => 'datetime',
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
     * الفاتورة المرتبطة بهذه القراءة (تشمل الملغاة)
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * الفاتورة الفعّالة المرتبطة بهذه القراءة (تستثني الملغاة).
     * تُستخدم لتحديد إن كانت القراءة تحتاج فاتورة جديدة بعد
     * إلغاء فاتورتها السابقة وإعادة اعتمادها.
     */
    public function activeInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class)
            ->where('invoice_status', '!=', Invoice::STATUS_CANCELLED);
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
     * العلاقة مع المستخدم الذي اعتمد القراءة
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Accessor للحصول على اسم حالة القراءة (يقرأ من الثوابت - ثابت رقم 28)
     */
    public function getReadingStatusNameAttribute(): string
    {
        $details = ConstantsHelper::get(28);
        $match   = $details->firstWhere('value', (string) $this->reading_status);
        if ($match) {
            return $match->label;
        }
        return match((int) $this->reading_status) {
            1 => 'طبيعية', 2 => 'غير طبيعية',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor للحصول على اسم إجراء القراءة (يقرأ من الثوابت - ثابت رقم 29)
     */
    public function getActionStatusNameAttribute(): string
    {
        $details = ConstantsHelper::get(29);
        $match   = $details->firstWhere('value', (string) $this->action_status);
        if ($match) {
            return $match->label;
        }
        return match((int) $this->action_status) {
            0 => 'غير معتمدة', 1 => 'معتمدة', 2 => 'مفوترة',
            default => 'غير محدد',
        };
    }

    /**
     * التحقق من إمكانية التعديل أو الحذف (غير معتمدة فقط)
     */
    public function isEditable(): bool
    {
        return $this->action_status === self::ACTION_STATUS_PENDING;
    }

    /**
     * التحقق من إمكانية الاعتماد (غير معتمدة فقط)
     */
    public function isApprovable(): bool
    {
        return $this->action_status === self::ACTION_STATUS_PENDING;
    }

    /**
     * هل هذه القراءة غير طبيعية؟
     */
    public function isAbnormal(): bool
    {
        return $this->reading_status === self::READING_STATUS_ABNORMAL;
    }

    /**
     * تحديد حالة القراءة تلقائياً بناءً على قيمة الاستهلاك.
     * تُعدّ غير طبيعية إذا كانت <= 0 أو تجاوزت الحد الأقصى المضبوط في الإعدادات.
     */
    public static function determineReadingStatus(float $consumptionKwh): int
    {
        if ($consumptionKwh <= 0) {
            return self::READING_STATUS_ABNORMAL;
        }

        $maxKwh = (float) Setting::get('abnormal_max_kwh', 9999);
        if ($maxKwh > 0 && $consumptionKwh > $maxKwh) {
            return self::READING_STATUS_ABNORMAL;
        }

        return self::READING_STATUS_NORMAL;
    }

    /**
     * هل القراءة تمنع الاعتماد الجماعي؟
     * الاستهلاك السالب أو المتجاوز للحد الأقصى يحتاج تعديلاً قبل الاعتماد.
     * الاستهلاك الصفري غير طبيعي لكنه مسموح ضمن الاعتماد الجماعي.
     */
    public function blocksBulkApproval(): bool
    {
        if (!$this->isAbnormal()) {
            return false;
        }
        return (float) $this->consumption_kwh !== 0.0;
    }

    /**
     * هل يمكن تحديدها واعتمادها من شاشة القائمة؟
     */
    public function canBulkApprove(): bool
    {
        return $this->isApprovable() && !$this->blocksBulkApproval();
    }

    /**
     * تحديد القراءة الأولى لكل مشترك ضمن مجموعة (لتجنب استعلامات متكررة).
     */
    public static function markFirstReadings(iterable $readings): void
    {
        $subscriberIds = collect($readings)->pluck('subscriber_id')->unique()->filter()->values();
        if ($subscriberIds->isEmpty()) {
            return;
        }

        $firstIds = static::whereIn('subscriber_id', $subscriberIds)
            ->orderBy('reading_date')
            ->orderBy('id')
            ->get(['id', 'subscriber_id'])
            ->groupBy('subscriber_id')
            ->map(fn ($group) => $group->first()->id);

        foreach ($readings as $reading) {
            $reading->setAttribute(
                'is_first_reading',
                $firstIds->get($reading->subscriber_id) === $reading->id
            );
        }
    }

    /**
     * هل هذه أول قراءة مسجّلة للمشترك؟
     */
    public function isFirstForSubscriber(): bool
    {
        if (array_key_exists('is_first_reading', $this->attributes)) {
            return (bool) $this->attributes['is_first_reading'];
        }

        $firstId = static::where('subscriber_id', $this->subscriber_id)
            ->orderBy('reading_date')
            ->orderBy('id')
            ->value('id');

        return $firstId === $this->id;
    }

    /**
     * القراءة السابقة للعرض: للقراءة الأولى تُستخدم القراءة الافتتاحية إن وُجدت.
     */
    public function getDisplayPreviousReading(): float
    {
        $previous = (float) $this->previous_reading;
        if ($previous > 0) {
            return $previous;
        }

        $opening = $this->subscriber?->opening_reading;
        if ($opening !== null && (float) $opening > 0 && $this->isFirstForSubscriber()) {
            return (float) $opening;
        }

        return $previous;
    }

    /**
     * الاستهلاك للعرض محسوباً من القراءة السابقة المعروضة.
     */
    public function getDisplayConsumptionKwh(): float
    {
        return round((float) $this->current_reading - $this->getDisplayPreviousReading(), 2);
    }

    /**
     * رقم العداد للعرض (يصحّح الخلط الشائع بين القراءة الافتتاحية ورقم العداد).
     */
    public function getDisplayMeterNumber(): string
    {
        $subscriberMeter = trim((string) ($this->subscriber?->meter_number ?? ''));
        $meter = trim((string) ($this->meter_number ?? ''));
        $opening = $this->subscriber?->opening_reading;

        if ($opening !== null && $meter !== '' && (float) $meter === (float) $opening) {
            return $subscriberMeter;
        }

        return $meter !== '' ? $meter : $subscriberMeter;
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
        $date   = date('Ymd');
        $prefix = $date . '-';

        // نستخدم withTrashed حتى لا تُعاد الأرقام المستخدمة في سجلات محذوفة
        // ونقفل الصف للقراءة لتجنب التكرار عند الإدراج المتزامن
        $last = static::withTrashed()
            ->where('reading_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('reading_number');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $seq   = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
