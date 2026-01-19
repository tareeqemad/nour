<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelConsumptionSummary extends Model
{
    protected $table = 'fuel_consumption_summary';

    protected $fillable = [
        'generator_id',
        'operator_id',
        'summary_date',
        'summary_type',
        'total_fuel_consumed',
        'total_energy_produced',
        'avg_efficiency',
        'operation_hours',
        'operation_count',
        'avg_load_percentage',
        'max_energy_produced',
        'min_energy_produced',
        'total_fuel_cost',
        'avg_fuel_price',
        'total_revenue',
        'avg_tariff_price',
    ];

    protected function casts(): array
    {
        return [
            'summary_date' => 'date',
            'total_fuel_consumed' => 'decimal:2',
            'total_energy_produced' => 'decimal:2',
            'avg_efficiency' => 'decimal:2',
            'operation_hours' => 'decimal:2',
            'avg_load_percentage' => 'decimal:2',
            'max_energy_produced' => 'decimal:2',
            'min_energy_produced' => 'decimal:2',
            'total_fuel_cost' => 'decimal:2',
            'avg_fuel_price' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'avg_tariff_price' => 'decimal:4',
        ];
    }

    /**
     * المولد (بما في ذلك المحذوفة soft delete)
     * للاستخدام في السجلات التاريخية
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(Generator::class)->withTrashed();
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
