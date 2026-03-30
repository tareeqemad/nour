<?php

namespace App\Models;

use App\Helpers\ConstantsHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MinimumChargeRule extends Model
{
    protected $fillable = ['operator_id', 'ampere', 'phase_type', 'minimum_charge'];

    protected function casts(): array
    {
        return [
            'operator_id'    => 'integer',
            'ampere'         => 'integer',
            'phase_type'     => 'integer',
            'minimum_charge' => 'decimal:2',
        ];
    }

    /**
     * العلاقة مع المشغل
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * اسم نوع الفاز للعرض
     */
    public function getPhaseNameAttribute(): string
    {
        return $this->phase_type === 2 ? 'ثلاثي (3 فاز)' : 'أحادي (1 فاز)';
    }

    /**
     * الحصول على الحد الأدنى لأمبير ونوع فاز ومشغل معين.
     */
    public static function getMinCharge(int $ampere, int $phase, int $operatorId = 0, float $fallback = 0): float
    {
        if ($operatorId <= 0) {
            return $fallback;
        }

        $rules = self::cachedForOperator($operatorId);
        return (float) ($rules[$ampere][$phase] ?? $fallback);
    }

    /**
     * إرجاع قواعد مشغل معين كـ [ampere][phase_type] => minimum_charge
     */
    public static function cachedForOperator(int $operatorId): array
    {
        return Cache::remember("minimum_charge_rules_op_{$operatorId}", 3600, function () use ($operatorId) {
            $result = [];
            foreach (self::where('operator_id', $operatorId)->get() as $rule) {
                $result[$rule->ampere][$rule->phase_type] = (float) $rule->minimum_charge;
            }
            return $result;
        });
    }

    /**
     * مسح كاش مشغل معين أو الكل
     */
    public static function clearCache(?int $operatorId = null): void
    {
        if ($operatorId) {
            Cache::forget("minimum_charge_rules_op_{$operatorId}");
        } else {
            // مسح كاش كل المشغلين
            $operatorIds = DB::table('operators')->pluck('id');
            foreach ($operatorIds as $id) {
                Cache::forget("minimum_charge_rules_op_{$id}");
            }
        }
    }

    /**
     * مسح الكاش تلقائياً عند الحفظ أو الحذف
     */
    protected static function booted(): void
    {
        static::saved(fn(self $rule)   => self::clearCache($rule->operator_id));
        static::deleted(fn(self $rule) => self::clearCache($rule->operator_id));
    }

    /** رقم ثابت الأمبير في جدول الثوابت */
    public const AMPERE_CONSTANT_NUMBER = 31;

    /**
     * جلب قيم الأمبير من الثوابت
     */
    public static function getAmpereValues(): array
    {
        return ConstantsHelper::get(self::AMPERE_CONSTANT_NUMBER)
            ->pluck('value')
            ->map(fn($v) => intval($v))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * القيم الافتراضية للحد الأدنى (تُستخدم فقط عند الـ seed الأولي)
     */
    public const DEFAULT_CHARGES = [
        1  => [1 => 10.00, 2 => 30.00],
        2  => [1 => 10.00, 2 => 30.00],
        3  => [1 => 10.00, 2 => 30.00],
        4  => [1 => 10.00, 2 => 30.00],
        6  => [1 => 20.00, 2 => 60.00],
        10 => [1 => 50.00, 2 => 150.00],
        16 => [1 => 50.00, 2 => 300.00],
    ];

    /**
     * إنشاء القواعد الافتراضية لمشغل معين
     * القيم من DEFAULT_CHARGES إن وُجدت، وإلا 0
     */
    public static function seedForOperator(int $operatorId): void
    {
        $ampereValues = self::getAmpereValues();

        foreach ($ampereValues as $ampere) {
            foreach ([1, 2] as $phaseType) {
                $defaultCharge = self::DEFAULT_CHARGES[$ampere][$phaseType] ?? 0;
                self::firstOrCreate(
                    ['operator_id' => $operatorId, 'ampere' => $ampere, 'phase_type' => $phaseType],
                    ['minimum_charge' => $defaultCharge]
                );
            }
        }
        self::clearCache($operatorId);
    }

    /**
     * إضافة قاعدة أمبير جديد لكل المشغلين (تُستدعى عند إضافة أمبير جديد بالثوابت)
     */
    public static function syncNewAmpere(int $ampere): void
    {
        $operatorIds = DB::table('operators')->pluck('id');

        foreach ($operatorIds as $operatorId) {
            foreach ([1, 2] as $phaseType) {
                self::firstOrCreate(
                    ['operator_id' => $operatorId, 'ampere' => $ampere, 'phase_type' => $phaseType],
                    ['minimum_charge' => 0]
                );
            }
        }
    }
}
