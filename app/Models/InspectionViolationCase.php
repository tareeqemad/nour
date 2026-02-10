<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionViolationCase extends Model
{
    protected $table = 'inspection_violation_cases';

    public const STATUS_ENTRY = 1;   // ادخال
    public const STATUS_RULED = 2; // محكومة
    public const STATUS_ENDED = 3;  // منتهية

    protected $fillable = [
        'governorate_id',
        'subscription_number',
        'subscriber_name',
        'beneficiary_name',
        'case_date',
        'status',
        'statement',
        'operator_id',
        'generation_unit_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'case_date' => 'date',
            'status' => 'integer',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ENTRY => 'ادخال',
            self::STATUS_RULED => 'محكومة',
            self::STATUS_ENDED => 'منتهية',
        ];
    }

    public static function statusCssClass(int $status): string
    {
        switch ((int) $status) {
            case self::STATUS_ENTRY: return 'danger';
            case self::STATUS_RULED: return 'warning';
            case self::STATUS_ENDED: return 'success';
            default: return 'secondary';
        }
    }

    public function governorateDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'governorate_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function generationUnit(): BelongsTo
    {
        return $this->belongsTo(GenerationUnit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
