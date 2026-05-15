<?php

namespace App\Helpers;

class PalestinianIdHelper
{
    /**
     * التحقق من صحة رقم الهوية الفلسطينية
     *
     * يتكون رقم الهوية من 9 أرقام، والرقم الأخير هو رقم تحقق (check digit)
     * يتم حسابه باستخدام خوارزمية مشابهة لـ Luhn:
     *  1. لكل رقم من الأرقام الثمانية الأولى:
     *     - إذا كان موقعه فردي (1, 3, 5, 7) يضرب في 2
     *     - إذا كان موقعه زوجي (2, 4, 6, 8) يضرب في 1
     *     - إذا كانت النتيجة أكبر من 9 تجمع خاناتها
     *  2. تجمع كل النواتج
     *  3. رقم التحقق = (10 - (المجموع % 10)) % 10
     *  4. يقارن مع الرقم التاسع
     *
     * @param  string|int|null  $id  رقم الهوية المراد التحقق منه
     */
    public static function isValid(string|int|null $id): bool
    {
        if ($id === null) {
            return false;
        }

        $id = trim((string) $id);

        if (! preg_match('/^\d{9}$/', $id)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $digit = (int) $id[$i];
            $product = $digit * (($i % 2 === 0) ? 1 : 2);

            if ($product > 9) {
                $product = intdiv($product, 10) + ($product % 10);
            }

            $sum += $product;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === (int) $id[8];
    }
}
