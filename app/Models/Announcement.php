<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'description',
        'announcement_date',
        'start_date',
        'end_date',
        'is_featured',
        'is_visible',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'announcement_date' => 'date',
            'start_date'        => 'date',
            'end_date'          => 'date',
            'is_featured'       => 'boolean',
            'is_visible'        => 'boolean',
        ];
    }

    // ===== Relationships =====

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // ===== Scopes =====

    /** الإعلانات الظاهرة (غير المخفية) */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_visible', true);
    }

    /** الإعلانات المميزة */
    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    /** ضمن نافذة العرض الزمنية (من – إلى) شاملةً اليوم */
    public function scopeActiveOn(Builder $q, $date = null): Builder
    {
        $date = $date ?: now()->toDateString();
        return $q->whereDate('start_date', '<=', $date)
                 ->whereDate('end_date', '>=', $date);
    }

    /** كل الإعلانات الظاهرة + ضمن النافذة الزمنية الحالية */
    public function scopePublic(Builder $q): Builder
    {
        return $q->visible()->activeOn();
    }

    // ===== Helpers =====

    /** هل الإعلان حالياً منتهي الصلاحية؟ */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /** هل الإعلان حالياً لم يبدأ بعد؟ */
    public function isUpcoming(): bool
    {
        return $this->start_date && $this->start_date->isFuture();
    }

    /** هل الإعلان فعال الآن (ظاهر + ضمن النافذة)؟ */
    public function isActive(): bool
    {
        if (! $this->is_visible) {
            return false;
        }
        $today = now()->startOfDay();
        return $this->start_date <= $today && $this->end_date >= $today;
    }
}
