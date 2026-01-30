<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ElectricityTariffPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'start_date',
        'end_date',
        'price_per_kwh',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price_per_kwh' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * المستخدم الذي أضاف السعر
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * الحصول على سعر التعرفة النشط العام في تاريخ معين
     * الأسعار عامة لجميع المشغلين
     * يتم البحث عن آخر سعر نشط حسب التاريخ (آخر تحديث)
     * 
     * المنطق:
     * 1. البحث عن الأسعار النشطة التي start_date <= التاريخ المطلوب
     * 2. والتحقق من أن end_date >= التاريخ المطلوب (أو null)
     * 3. ترتيب حسب start_date (الأحدث أولاً) ثم created_at (آخر تحديث أولاً)
     * 
     * @param Carbon $date التاريخ المطلوب
     * @return self|null
     */
    public static function getActivePriceForDate(Carbon $date): ?self
    {
        // الأسعار عامة لجميع المشغلين
        // البحث عن آخر سعر نشط حسب التاريخ (آخر تحديث)
        return self::where('is_active', true)
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date->format('Y-m-d'));
            })
            ->orderBy('start_date', 'desc') // آخر تاريخ بدء أولاً
            ->orderBy('created_at', 'desc') // إذا كان نفس start_date، نأخذ آخر تحديث
            ->orderBy('id', 'desc') // كترتيب نهائي للتأكد
            ->first();
    }

    /**
     * الحصول على سعر التعرفة النشط الحالي
     */
    public static function getCurrentActivePrice(): ?self
    {
        return self::getActivePriceForDate(Carbon::now());
    }
}
