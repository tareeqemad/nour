<?php

namespace App\Models;

use App\Governorate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintSuggestion extends Model
{
    protected $table = 'complaints_suggestions';

    protected $fillable = [
        'type',
        'name',
        'phone',
        'email',
        'governorate',
        'operator_id', // المشغل المرتبط بالشكوى
        'generator_id', // المولد المرتبط (اختياري - لتحديد المولد المحدد)
        'subject',
        'message',
        'image',
        'status',
        'response',
        'responded_by',
        'responded_at',
        'tracking_code',
        'closed_by_operator', // هل تم إغلاقها من قبل المشغل
        'closed_at', // تاريخ الإغلاق من قبل المشغل
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'closed_at' => 'datetime',
            'governorate' => Governorate::class,
            'closed_by_operator' => 'boolean',
        ];
    }

    /**
     * المستخدم الذي رد على الطلب
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * المشغل المرتبط بالطلب
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * المولد المرتبط بالطلب (اختياري - لتحديد المولد المحدد)
     * بما في ذلك المحذوفة soft delete للاستخدام في السجلات التاريخية
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class)->withTrashed();
    }

    /**
     * إنشاء رمز تتبع فريد
     */
    public static function generateTrackingCode(): string
    {
        do {
            $code = 'CS-'.strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('tracking_code', $code)->exists());

        return $code;
    }

    /**
     * الحصول على حالة الطلب بالعربية
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد المعالجة',
            'resolved' => 'تم الحل',
            'rejected' => 'مرفوض',
            default => 'غير معروف',
        };
    }

    /**
     * الحصول على نوع الطلب بالعربية
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'complaint' ? 'شكوى' : 'مقترح';
    }

    /**
     * الحصول على اسم المحافظة
     */
    public function getGovernorateLabel(): ?string
    {
        return $this->governorate?->label();
    }
}
