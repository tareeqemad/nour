<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedPortalRequest extends Model
{
    protected $fillable = [
        'app_no',
        'applicant_id',
        'applicant_name',
        'phone',
        'user_id',
        'operator_id',
        'status',
        'notes',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public static function isProcessed(string $appNo): bool
    {
        return static::where('app_no', $appNo)->where('status', 'success')->exists();
    }
}
