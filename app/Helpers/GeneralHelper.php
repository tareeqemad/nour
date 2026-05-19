<?php

namespace App\Helpers;

use App\Models\Operator;
use Illuminate\Support\Collection;

class GeneralHelper
{
    /**
     * الحصول على المشغلين التابعين لمحافظة معينة
     *
     * @param int $governorateValue رقم المحافظة (10, 20, 30, 40)
     * @param bool $activeOnly إذا كان true، يرجع فقط المشغلين النشطين
     * @return Collection
     */
    public static function getOperatorsByGovernorate(int $governorateValue, bool $activeOnly = true): Collection
    {
        // الحصول على ID المحافظة من الثوابت
        $governorateDetail = \App\Helpers\ConstantsHelper::get(1)
            ->where('value', (string) $governorateValue)
            ->first();
        
        if (!$governorateDetail) {
            return collect();
        }
        
        $query = Operator::whereHas('generationUnits', function($q) use ($governorateDetail) {
            $q->where('governorate_id', $governorateDetail->id);
        });

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * الحصول على المشغلين التابعين لمحافظة معينة مع معلومات أساسية فقط
     *
     * @param int $governorateValue رقم المحافظة
     * @param bool $activeOnly إذا كان true، يرجع فقط المشغلين النشطين
     * @return Collection
     */
    public static function getOperatorsByGovernorateSimple(int $governorateValue, bool $activeOnly = true): Collection
    {
        // الحصول على ID المحافظة من الثوابت
        $governorateDetail = \App\Helpers\ConstantsHelper::get(1)
            ->where('value', (string) $governorateValue)
            ->first();
        
        if (!$governorateDetail) {
            return collect();
        }
        
        $query = Operator::select('operators.id', 'operators.name', 'operators.status')
            ->whereHas('generationUnits', function($q) use ($governorateDetail) {
                $q->where('governorate_id', $governorateDetail->id);
            });

        if ($activeOnly) {
            $query->where('operators.status', 'active');
        }

        return $query->orderBy('operators.name')->get();
    }

    /**
     * تنسيق رقم كبير بالاختصارات (K للآلاف، M للملايين)
     * - أقل من 1,000: يُعرض كاملاً (مثلاً "640")
     * - 1,000 إلى أقل من 1,000,000: يُعرض بـ K (مثلاً "1.5K")
     * - 1,000,000 فأكثر: يُعرض بـ M (مثلاً "2.3M")
     *
     * @param float|int|null $value
     * @param int $decimals عدد الخانات العشرية للقيم المختصرة
     */
    public static function formatCompactNumber($value, int $decimals = 1): string
    {
        $n = (float) ($value ?? 0);
        if ($n >= 1_000_000) {
            return number_format($n / 1_000_000, $decimals) . 'M';
        }
        if ($n >= 1_000) {
            return number_format($n / 1_000, $decimals) . 'K';
        }
        return number_format($n);
    }
}






