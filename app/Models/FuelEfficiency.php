<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ConstantDetail;
use App\Models\Operator;
use App\Models\GenerationUnit;
use App\Traits\TracksUser;

class FuelEfficiency extends Model
{
    use SoftDeletes, TracksUser;

    protected $fillable = [
        'operator_id',
        'generation_unit_id',
        'generator_id',
        'consumption_date',
        'operating_hours',
        'fuel_price_per_liter',
        'fuel_consumed',
        'fuel_efficiency_percentage',
        'fuel_efficiency_comparison_id', // ID من constant_details - ثابت Master رقم 17 (مقارنة كفاءة الوقود)
        'energy_distribution_efficiency',
        'energy_efficiency_comparison_id', // ID من constant_details - ثابت Master رقم 18 (مقارنة كفاءة الطاقة)
        'total_operating_cost',
    ];

    protected function casts(): array
    {
        return [
            'consumption_date' => 'date',
            'operating_hours' => 'decimal:2',
            'fuel_price_per_liter' => 'decimal:2',
            'fuel_consumed' => 'decimal:2',
            'fuel_efficiency_percentage' => 'decimal:2',
            'energy_distribution_efficiency' => 'decimal:2',
            'total_operating_cost' => 'decimal:2',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function generationUnit(): BelongsTo
    {
        return $this->belongsTo(GenerationUnit::class);
    }

    /**
     * المولد (بما في ذلك المحذوفة soft delete)
     * للاستخدام في السجلات التاريخية
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class)->withTrashed();
    }

    // Relationships للثوابت - ثابت Master رقم 17 (مقارنة كفاءة الوقود)
    public function fuelEfficiencyComparisonDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'fuel_efficiency_comparison_id');
    }

    // Relationships للثوابت - ثابت Master رقم 18 (مقارنة كفاءة الطاقة)
    public function energyEfficiencyComparisonDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'energy_efficiency_comparison_id');
    }
}
