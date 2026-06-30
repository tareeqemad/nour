<?php

namespace App\Models\Madmoun;

use App\Models\Operator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * مثال (دور مبرمج مضمون): موديل "بوليصة ضمان".
 * يرث MadmounModel ⟶ اسم الجدول تلقائياً madmoun_policies.
 */
class Policy extends MadmounModel
{
    use SoftDeletes;

    protected $fillable = [
        'operator_id',
        'policy_number',
        'beneficiary',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    // ربط بنواة نور — نفس قاعدة البيانات، علاقة مباشرة.
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
