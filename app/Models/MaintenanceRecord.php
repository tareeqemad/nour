<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ConstantDetail;
use App\Traits\TracksUser;

class MaintenanceRecord extends Model
{
    use SoftDeletes, TracksUser;

    protected $fillable = [
        'generator_id',
        'maintenance_type_id', // ID من constant_details - ثابت Master رقم 12 (نوع الصيانة)
        'maintenance_date',
        'start_time',
        'end_time',
        'technician_name',
        'work_performed',
        'downtime_hours',
        'parts_cost',
        'labor_hours',
        'labor_rate_per_hour',
        'maintenance_cost',
        'next_maintenance_type_id', // ID من constant_details - ثابت Master رقم 12 (نوع الصيانة)
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'downtime_hours' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'labor_hours' => 'decimal:2',
            'labor_rate_per_hour' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
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

    // Relationships للثوابت - ثابت Master رقم 12 (نوع الصيانة)
    public function maintenanceTypeDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'maintenance_type_id');
    }

    // Relationships للثوابت - ثابت Master رقم 12 (نوع الصيانة القادمة)
    public function nextMaintenanceTypeDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'next_maintenance_type_id');
    }
}
