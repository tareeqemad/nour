<?php

namespace App\Traits;

/**
 * Trait لتنظيف مدخلات البحث
 * 
 * يمكن استخدامه في أي Controller يحتاج تنظيف مدخلات البحث
 */
trait SanitizesInput
{
    /**
     * تنظيف مدخلات البحث لمنع SQL Injection
     * 
     * @param string|null $input
     * @return string
     */
    protected function sanitizeSearchInput(?string $input): string
    {
        if (empty($input)) {
            return '';
        }

        // إزالة المسافات الزائدة
        $input = trim($input);
        
        // إزالة HTML tags
        $input = strip_tags($input);
        
        // إزالة الأحرف الخاصة التي قد تستخدم في SQL Injection
        // لكن نترك % و _ لأنها مفيدة في LIKE queries
        $input = preg_replace('/[;\'"\\\]/', '', $input);
        
        // تحديد طول أقصى للبحث (255 حرف)
        $input = mb_substr($input, 0, 255);
        
        return $input;
    }
}
