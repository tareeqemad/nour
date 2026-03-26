<?php

namespace App\Models;

use App\Governorate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintSuggestion extends Model
{
    use SoftDeletes;

    protected $table = 'complaints_suggestions';

    protected $fillable = [
        'type',
        'name',
        'phone',
        'email',
        'governorate',
        'operator_id',
        'generator_id',
        'subject',
        'message',
        'image',
        'status',
        'response',
        'responded_by',
        'responded_at',
        'tracking_code',
        'closed_by_operator',
        'closed_at',
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

    // ── العلاقات ──

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * المولد المرتبط — بدون withTrashed بالـ default
     * استخدم ->generator()->withTrashed()->first() لما بتحتاج المحذوفة
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class);
    }

    // ── Helpers ──

    public static function generateTrackingCode(): string
    {
        do {
            $code = 'CS-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('tracking_code', $code)->exists());

        return $code;
    }

    // ── Accessors ──

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

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'complaint' ? 'شكوى' : 'مقترح';
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type === 'complaint' ? 'danger' : 'info';
    }

    public function getGovernorateLabel(): ?string
    {
        return $this->governorate?->label();
    }
}
