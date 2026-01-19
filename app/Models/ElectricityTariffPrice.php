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
        'operator_id',
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
            'price_per_kwh' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * الحصول على سعر التعرفة النشط لمشغل معين في تاريخ معين
     */
    public static function getActivePriceForDate(int $operatorId, Carbon $date): ?self
    {
        return self::where('operator_id', $operatorId)
            ->where('is_active', true)
            ->where('start_date', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date->format('Y-m-d'));
            })
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * الحصول على سعر التعرفة النشط الحالي لمشغل معين
     */
    public static function getCurrentActivePrice(int $operatorId): ?self
    {
        return self::getActivePriceForDate($operatorId, Carbon::now());
    }
}
