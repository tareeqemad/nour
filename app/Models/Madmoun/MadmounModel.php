<?php

namespace App\Models\Madmoun;

use Illuminate\Database\Eloquent\Model;

/**
 * الموديل الأساس لكل موديلات مضمون.
 *
 * القاعدة: كل جداول مضمون تبدأ بالبادئة "madmoun_".
 * هذا الأساس يشتق اسم الجدول تلقائياً بالبادئة إذا لم تُحدّد $table صراحةً،
 * فمثلاً موديل App\Models\Madmoun\Policy ⟶ جدول madmoun_policies.
 *
 * مبرمج مضمون: ورّث موديلاتك من هنا، وأنشئ جداولك في
 * database/migrations/madmoun ببادئة madmoun_.
 */
abstract class MadmounModel extends Model
{
    /**
     * البادئة الموحّدة لجداول منصة مضمون.
     */
    public const TABLE_PREFIX = 'madmoun_';

    public function getTable(): string
    {
        // إذا حُدّد $table صراحةً في الموديل، نحترمه كما هو.
        if (isset($this->table)) {
            return $this->table;
        }

        // وإلا نشتق الاسم الافتراضي ونضيف البادئة إن لم تكن موجودة.
        $default = parent::getTable();

        return str_starts_with($default, self::TABLE_PREFIX)
            ? $default
            : self::TABLE_PREFIX.$default;
    }
}
