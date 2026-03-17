<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class EmployeeDiscountRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'start_date',
        'end_date',
        'discount_rate',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date'    => 'date',
            'end_date'      => 'date',
            'discount_rate' => 'decimal:2',
            'is_active'     => 'boolean',
        ];
    }

    /**
     * المستخدم الذي أضاف النسبة
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * الحصول على نسبة خصم الموظفين النشطة في تاريخ معين.
     *
     * المنطق:
     * 1. البحث عن النسب النشطة التي start_date <= التاريخ المطلوب
     * 2. والتحقق من أن end_date >= التاريخ المطلوب (أو null)
     * 3. ترتيب حسب start_date (الأحدث أولاً) ثم created_at (آخر تحديث أولاً)
     *
     * @param  Carbon  $date  التاريخ المطلوب
     * @return self|null
     */
    public static function getActiveRateForDate(Carbon $date): ?self
    {
        return self::where('is_active', true)
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date->format('Y-m-d'));
            })
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * الحصول على نسبة الخصم النشطة الحالية
     */
    public static function getCurrentActiveRate(): ?self
    {
        return self::getActiveRateForDate(Carbon::now());
    }
}
