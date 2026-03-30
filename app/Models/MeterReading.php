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
     * الفاتورة المرتبطة بهذه القراءة
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
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
     * تُعدّ غير طبيعية إذا كانت الاستهلاك <= 0 أو تجاوز الحد الأقصى المضبوط في الإعدادات.
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
