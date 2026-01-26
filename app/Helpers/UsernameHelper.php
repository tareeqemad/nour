<?php

namespace App\Helpers;

use App\Enums\Role;
use App\Models\User;

class UsernameHelper
{
    /**
     * توليد username تلقائياً بناءً على الدور والاسم
     * 
     * @param Role $role دور المستخدم
     * @param string $nameEn الاسم بالإنجليزية
     * @param string|null $fallbackIdNumber رقم الهوية كبديل في حالة عدم وجود اسم صالح
     * @return string username فريد
     */
    public static function generate(Role $role, string $nameEn, ?string $fallbackIdNumber = null): string
    {
        // تحديد البادئة حسب الدور
        $prefix = match($role) {
            Role::SuperAdmin => 'sp_',
            Role::Admin => 'a_',
            Role::EnergyAuthority => 'ea_',
            Role::CompanyOwner => 'op_',
            Role::Technician => 't_',
            Role::CivilDefense => 'cd_',
            default => 'user_',
        };
        
        // تنظيف الاسم من الأحرف العربية والأحرف الخاصة
        $cleanedName = \App\Providers\AppServiceProvider::cleanStringStatic($nameEn);
        
        // إذا كان الاسم فارغاً أو يحتوي على أحرف عربية فقط، نحاول تحويله
        // إزالة الأحرف غير اللاتينية أولاً للتحقق
        $latinOnly = preg_replace('/[^a-z0-9\s]/i', '', $cleanedName);
        if (empty(trim($latinOnly)) && !empty($cleanedName)) {
            // الاسم يحتوي على أحرف عربية فقط، نحاول تحويله باستخدام transliteration بسيط
            $cleanedName = self::transliterateArabicToLatin($cleanedName);
        }
        
        $nameParts = explode(' ', trim($cleanedName));
        $nameParts = array_filter($nameParts, function($part) {
            return !empty(trim($part));
        });
        $nameParts = array_values($nameParts); // إعادة ترقيم المصفوفة
        
        // استخراج أول حرف من الاسم الأول + الاسم الأخير كاملاً
        if (count($nameParts) >= 2) {
            // أول حرف من الاسم الأول
            $firstChar = strtolower(substr(trim($nameParts[0]), 0, 1));
            // الاسم الأخير كاملاً (اسم العائلة)
            $lastName = strtolower(trim($nameParts[count($nameParts) - 1]));
            // إزالة الأحرف الخاصة والمسافات من اسم العائلة (يحافظ على a-z0-9 فقط)
            $lastName = preg_replace('/[^a-z0-9]/', '', $lastName);
            $usernameBase = $firstChar . $lastName;
        } else {
            // إذا كان اسم واحد فقط، استخدم أول 8 أحرف
            $usernameBase = strtolower(preg_replace('/[^a-z0-9]/', '', $cleanedName));
            $usernameBase = substr($usernameBase, 0, 8);
        }
        
        // التأكد من أن usernameBase ليس فارغاً
        if (empty($usernameBase)) {
            // استخدام رقم الهوية كبديل
            $usernameBase = $fallbackIdNumber ? substr($fallbackIdNumber, -4) : 'user';
        }
        
        // إضافة البادئة
        $username = $prefix . $usernameBase;
        
        // التأكد من أن username فريد (استبعاد المستخدمين المحذوفين)
        $counter = 1;
        $originalUsername = $username;
        while (User::where('username', $username)->whereNull('deleted_at')->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * تحويل الأحرف العربية إلى لاتينية (transliteration بسيط)
     * يستخدم لتحويل الأسماء العربية إلى أسماء لاتينية لتوليد username
     * 
     * @param string $arabicText النص العربي
     * @return string النص المحول إلى لاتينية
     */
    public static function transliterateArabicToLatin(string $arabicText): string
    {
        // خريطة بسيطة للأحرف العربية الشائعة إلى لاتينية
        $transliterationMap = [
            'أ' => 'a', 'ا' => 'a', 'إ' => 'i', 'آ' => 'aa',
            'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j',
            'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'th',
            'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh',
            'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z',
            'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q',
            'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'ه' => 'h', 'و' => 'w', 'ي' => 'y', 'ى' => 'a',
            'ة' => 'h', 'ئ' => 'y', 'ء' => '', 'ؤ' => 'w',
        ];

        $result = '';
        $text = mb_strtolower($arabicText, 'UTF-8');
        
        for ($i = 0; $i < mb_strlen($text, 'UTF-8'); $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            if (isset($transliterationMap[$char])) {
                $result .= $transliterationMap[$char];
            } elseif (preg_match('/[a-z0-9\s]/', $char)) {
                // الاحتفاظ بالأحرف اللاتينية والأرقام والمسافات
                $result .= $char;
            }
            // تجاهل الأحرف الأخرى
        }
        
        return trim($result);
    }
}
