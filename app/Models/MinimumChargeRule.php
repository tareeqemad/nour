<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MinimumChargeRule extends Model
{
    protected $fillable = ['ampere', 'phase_type', 'minimum_charge'];

    protected function casts(): array
    {
        return [
            'ampere'         => 'integer',
            'phase_type'     => 'integer',
            'minimum_charge' => 'decimal:2',
        ];
    }

    /**
     * اسم نوع الفاز للعرض
     */
    public function getPhaseNameAttribute(): string
    {
        return $this->phase_type === 2 ? 'ثلاثي (3 فاز)' : 'أحادي (1 فاز)';
    }

    /**
     * الحصول على الحد الأدنى لأمبير ونوع فاز معين.
     * مع كاش لتجنب استعلامات متكررة.
     *
     * @param int $ampere
     * @param int $phase  1=أحادي, 2=ثلاثي
     * @param float $fallback القيمة الافتراضية إذا لم تُوجد قاعدة
     */
    public static function getMinCharge(int $ampere, int $phase, float $fallback = 0): float
    {
        $rules = self::allCached();
        return (float) ($rules[$ampere][$phase] ?? $fallback);
    }

    /**
     * إرجاع كل القواعد مُنسَّقة كـ [ampere][phase_type] => minimum_charge
     * مع كاش لمدة ساعة يُمسح عند أي تعديل.
     */
    public static function allCached(): array
    {
        return Cache::remember('minimum_charge_rules', 3600, function () {
            $result = [];
            foreach (self::all() as $rule) {
                $result[$rule->ampere][$rule->phase_type] = (float) $rule->minimum_charge;
            }
            return $result;
        });
    }

    /**
     * مسح الكاش (يُستدعى بعد أي تعديل)
     */
    public static function clearCache(): void
    {
        Cache::forget('minimum_charge_rules');
    }

    /**
     * مسح الكاش تلقائياً عند الحفظ أو الحذف
     */
    protected static function booted(): void
    {
        static::saved(fn()  => self::clearCache());
        static::deleted(fn() => self::clearCache());
    }
}
