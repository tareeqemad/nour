<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConstantDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'constant_master_id',
        'parent_detail_id',
        'label',
        'code',
        'value',
        'notes',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(ConstantMaster::class, 'constant_master_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'parent_detail_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ConstantDetail::class, 'parent_detail_id')->where('is_active', true)->orderBy('order');
    }

    /**
     * الحصول على تفاصيل ثابت معين
     */
    public static function getByConstantNumber(int $constantNumber): \Illuminate\Support\Collection
    {
        $master = ConstantMaster::findByNumber($constantNumber);

        if (! $master) {
            return collect();
        }

        return $master->details;
    }

    /** Bootstrap badge/background color names that are safe to use */
    private const BADGE_COLOR_WHITELIST = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];

    /**
     * Get badge color for Bootstrap (bg-*). Uses value only if it's a valid Bootstrap color name.
     */
    public function getBadgeColor(): string
    {
        $value = $this->value ? trim((string) $this->value) : '';
        if ($value !== '' && in_array(strtolower($value), self::BADGE_COLOR_WHITELIST, true)) {
            return strtolower($value);
        }

        $code = strtolower($this->code ?? '');

        return match ($code) {
            'emergency' => 'danger',
            'periodic', 'preventive' => 'info',
            'major' => 'warning',
            'available', 'valid' => 'success',
            'expired' => 'danger',
            'not_available', 'pending' => 'warning',
            'within_standard' => 'success',
            'above' => 'warning',
            'below' => 'danger',
            default => 'secondary'
        };
    }
}
