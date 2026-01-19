<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ConstantDetail;
use App\Traits\TracksUser;

class ComplianceSafety extends Model
{
    use SoftDeletes, TracksUser;

    protected $fillable = [
        'operator_id',
        'safety_certificate_status_id', // ID من constant_details - ثابت Master رقم 13 (حالة شهادة السلامة)
        'last_inspection_date',
        'inspection_authority',
        'inspection_result',
        'violations',
    ];

    protected function casts(): array
    {
        return [
            'last_inspection_date' => 'date',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    // Relationships للثوابت - ثابت Master رقم 13 (حالة شهادة السلامة)
    public function safetyCertificateStatusDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'safety_certificate_status_id');
    }
}
