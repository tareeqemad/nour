<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class Payment extends Model
{
    use HasFactory, SoftDeletes, TracksUser;

    protected $table = 'payments';

    // طرق الدفع
    const METHOD_CASH          = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHEQUE        = 'cheque';
    const METHOD_ONLINE        = 'online';

    public static function methodLabels(): array
    {
        return [
            self::METHOD_CASH          => 'نقداً',
            self::METHOD_BANK_TRANSFER => 'تحويل بنكي',
            self::METHOD_CHEQUE        => 'شيك',
            self::METHOD_ONLINE        => 'دفع إلكتروني',
        ];
    }

    protected $fillable = [
        'invoice_id',
        'subscriber_id',
        'payment_date',
        'amount_paid',
        'credit_applied',
        'overpayment',
        'payment_method',
        'receipt_number',
        'notes',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date'  => 'date',
            'amount_paid'   => 'decimal:2',
            'credit_applied'=> 'decimal:2',
            'overpayment'   => 'decimal:2',
        ];
    }

    // ===== العلاقات =====

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // ===== Accessors =====

    public function getMethodLabelAttribute(): string
    {
        return self::methodLabels()[$this->payment_method] ?? $this->payment_method;
    }
}
