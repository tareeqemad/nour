<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;
use App\Models\ElectricityTariffPrice;
use App\Models\EmployeeDiscountRate;
use App\Models\Setting;
use App\Helpers\ConstantsHelper;
use App\Models\MinimumChargeRule;
use App\Models\Payment;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, TracksUser;

    protected $table = 'invoices';

    // ثوابت حالة الفاتورة
    const STATUS_DRAFT     = 0; // مسودة
    const STATUS_ISSUED    = 1; // جديدة (مُصدَرة)
    const STATUS_PARTIAL   = 2; // مسددة جزئياً
    const STATUS_PAID      = 3; // مسددة
    const STATUS_CANCELLED = 4; // ملغاة

    protected $fillable = [
        'invoice_number',
        'subscriber_id',
        'meter_reading_id',
        'invoice_date',
        'consumption_period_days',
        'consumption_kwh',
        'price_per_kwh',
        'discount_rate',
        'invoice_amount',
        'minimum_charge',
        'previous_balance',
        'total_amount',
        'invoice_status',
        'due_date',
        'notes',
        'issued_by',
        'issued_at',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date'            => 'date',
            'due_date'                => 'date',
            'issued_at'               => 'datetime',
            'consumption_period_days' => 'integer',
            'consumption_kwh'         => 'decimal:2',
            'price_per_kwh'           => 'decimal:4',
            'discount_rate'           => 'decimal:2',
            'invoice_amount'          => 'decimal:2',
            'minimum_charge'          => 'decimal:2',
            'previous_balance'        => 'decimal:2',
            'total_amount'            => 'decimal:2',
            'invoice_status'          => 'integer',
        ];
    }

    // ===== العلاقات =====

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function meterReading(): BelongsTo
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ===== Payment Helpers =====

    /**
     * إجمالي المبالغ المسددة على هذه الفاتورة
     */
    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount_paid');
    }

    /**
     * المبلغ المتبقي على هذه الفاتورة
     */
    public function remainingAmount(): float
    {
        return max((float) $this->total_amount - $this->paidAmount(), 0);
    }

    /**
     * تحديث حالة الفاتورة بناءً على مجموع الدفعات.
     * يُستدعى بعد كل دفعة.
     */
    public function updateStatusFromPayments(): void
    {
        $paid = $this->paidAmount();
        $total = (float) $this->total_amount;

        if ($total <= 0 || $paid >= $total) {
            $this->invoice_status = self::STATUS_PAID;
        } elseif ($paid > 0) {
            $this->invoice_status = self::STATUS_PARTIAL;
        }
        $this->save();
    }

    /**
     * بعد تسجيل أو حذف دفعة: أعد احتساب الرصيد السابق وإجمالي كل مسودة
     * مفتوحة للمشترك — هذا يضمن أن أرقام المسودات دائماً محدّثة.
     */
    public static function refreshDraftsForSubscriber(int $subscriberId): void
    {
        $drafts = self::where('subscriber_id', $subscriberId)
            ->where('invoice_status', self::STATUS_DRAFT)
            ->orderBy('invoice_date')
            ->get();

        if ($drafts->isEmpty()) {
            return;
        }

        // الرصيد السابق التراكمي — يُحسب مرة واحدة لأن المسودات ليست مؤكدة بعد
        $freshPrevBalance = self::calculatePreviousBalance($subscriberId);

        foreach ($drafts as $draft) {
            // تطبيق قواعد الخصم والحد الأدنى
            $subscriber = $draft->subscriber;
            $draftDate = $draft->invoice_date ? \Carbon\Carbon::parse($draft->invoice_date) : null;
            [$freshDiscount, $freshMinCharge] = $subscriber
                ? self::applySubscriberRules($subscriber, (float)$draft->discount_rate, (float)$draft->minimum_charge, $draftDate)
                : [(float)$draft->discount_rate, (float)$draft->minimum_charge];

            $amounts = self::calculateAmounts(
                (float)$draft->consumption_kwh,
                (float)$draft->price_per_kwh,
                $freshDiscount,
                $freshMinCharge,
                $freshPrevBalance
            );

            $draft->update([
                'discount_rate'    => $freshDiscount,
                'minimum_charge'   => $freshMinCharge,
                'previous_balance' => round($freshPrevBalance, 2),
                'invoice_amount'   => $amounts['invoice_amount'],
                'total_amount'     => $amounts['total_amount'],
            ]);
        }
    }

    // ===== Accessors =====

    /**
     * هل الفاتورة متأخرة (صادرة وتجاوزت تاريخ الاستحقاق)
     */
    public function isOverdue(): bool
    {
        return $this->invoice_status === self::STATUS_ISSUED
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    /**
     * ثمن الاستهلاك (الخطوة الأولى): kwh × price × (1 - discount/100)
     */
    public function getConsumptionCostAttribute(): float
    {
        return round(
            (float)$this->consumption_kwh
            * (float)$this->price_per_kwh
            * (1 - ((float)$this->discount_rate / 100)),
            2
        );
    }

    /**
     * اسم حالة الفاتورة (يقرأ من الثوابت - ثابت رقم 30)
     */
    public function getStatusNameAttribute(): string
    {
        if ($this->isOverdue()) {
            return 'متأخرة';
        }
        $details = ConstantsHelper::get(30);
        $match   = $details->firstWhere('value', (string) $this->invoice_status);
        if ($match) {
            return $match->label;
        }
        return match ($this->invoice_status) {
            self::STATUS_DRAFT     => 'مسودة',
            self::STATUS_ISSUED    => 'جديدة',
            self::STATUS_PARTIAL   => 'مسددة جزئياً',
            self::STATUS_PAID      => 'مسددة',
            self::STATUS_CANCELLED => 'ملغاة',
            default                => 'غير معروف',
        };
    }

    /**
     * كلاس Bootstrap لشارة الحالة
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isOverdue()) {
            return 'danger';
        }
        return match ($this->invoice_status) {
            self::STATUS_DRAFT     => 'secondary',
            self::STATUS_ISSUED    => 'primary',
            self::STATUS_PARTIAL   => 'warning text-dark',
            self::STATUS_PAID      => 'success',
            self::STATUS_CANCELLED => 'dark',
            default                => 'secondary',
        };
    }

    /**
     * هل يمكن تعديل الفاتورة (مسودة فقط)
     */
    public function isEditable(): bool
    {
        return $this->invoice_status === self::STATUS_DRAFT;
    }

    /**
     * هل يمكن إلغاء الفاتورة
     */
    public function isCancellable(): bool
    {
        return in_array($this->invoice_status, [self::STATUS_DRAFT, self::STATUS_ISSUED]);
    }

    /**
     * هل يمكن إصدار الفاتورة (من مسودة)
     */
    public function isIssuable(): bool
    {
        return $this->invoice_status === self::STATUS_DRAFT;
    }

    // ===== Static Helpers =====

    /**
     * حساب الرصيد السابق (الصافي التراكمي) لمشترك.
     *
     * الرصيد = مجموع invoice_amount لكل الفواتير المُصدَرة/المسددة − مجموع كل الدفعات عليها.
     *   - موجب → مديون على المشترك (يُضاف على الفاتورة التالية)
     *   - سالب → دائن لصالح المشترك (يُخصم من الفاتورة التالية)
     *
     * نستخدم invoice_amount (وليس total_amount) لتفادي التكرار المتسلسل
     * لأن total_amount = invoice_amount + previous_balance.
     *
     * @param int      $subscriberId     معرّف المشترك
     * @param int|null $excludeInvoiceId فاتورة يتم استثناؤها (الفاتورة الحالية المراد حسابها)
     */
    public static function calculatePreviousBalance(int $subscriberId, ?int $excludeInvoiceId = null): float
    {
        // فقط الفواتير المؤكدة (غير المسودة وغير الملغاة)
        $invoiceQuery = self::where('subscriber_id', $subscriberId)
            ->whereNotIn('invoice_status', [self::STATUS_DRAFT, self::STATUS_CANCELLED]);

        if ($excludeInvoiceId) {
            $invoiceQuery->where('id', '!=', $excludeInvoiceId);
        }

        // مجموع قيم الفواتير (invoice_amount فقط)
        $totalInvoiced = (float) (clone $invoiceQuery)->sum('invoice_amount');

        // مجموع الدفعات على تلك الفواتير فقط (subquery)
        $totalPaid = (float) Payment::whereIn(
            'invoice_id',
            (clone $invoiceQuery)->select('id')
        )->sum('amount_paid');

        return round($totalInvoiced - $totalPaid, 2);
    }

    /**
     * توليد رقم فاتورة فريد بطريقة ذرية (Atomic) باستخدام جدول التسلسل.
     *
     * الآلية:
     *   INSERT INTO invoice_sequences (prefix, last_seq) VALUES (key, LAST_INSERT_ID(1))
     *   ON DUPLICATE KEY UPDATE last_seq = LAST_INSERT_ID(last_seq + 1)
     *
     * هذه عملية واحدة ذرية في MySQL: حتى لو حدث طلبان في نفس اللحظة تماماً
     * يضمن MySQL أن كل طلب يحصل على قيمة last_seq مختلفة.
     * يجب استدعاء هذه الدالة دائماً داخل DB::transaction لضمان الـ rollback
     * في حالة الفشل وعدم "استهلاك" تسلسل بدون فاتورة.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year   = now()->format('Y');
        $month  = now()->format('m');
        $key    = "{$prefix}-{$year}{$month}";

        // عملية ذرية: increment أو insert في خطوة واحدة
        \DB::statement(
            'INSERT INTO invoice_sequences (prefix, last_seq) VALUES (?, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE last_seq = LAST_INSERT_ID(last_seq + 1)',
            [$key]
        );

        $seq = \DB::selectOne('SELECT LAST_INSERT_ID() AS seq')->seq;

        return sprintf('%s-%s%s-%05d', $prefix, $year, $month, (int) $seq);
    }

    /**
     * جلب نسبة خصم موظفي الشركة من جدول employee_discount_rates.
     * يبحث عن النسبة النشطة في التاريخ المحدد (أو اليوم).
     * الفواتير القديمة تحتفظ بنسبتها الأصلية لأن البحث يكون بتاريخ الفاتورة.
     *
     * @param  \Carbon\Carbon|null  $date  تاريخ البحث (افتراضي: اليوم)
     * @return float
     */
    public static function getEmployeeDiscountRate(?\Carbon\Carbon $date = null): float
    {
        $date = $date ?? \Carbon\Carbon::now();
        $active = EmployeeDiscountRate::getActiveRateForDate($date);

        return $active ? (float) $active->discount_rate : 0;
    }

    /**
     * تطبيق قواعد الخصم والحد الأدنى على بيانات الفاتورة.
     * يُرجع [discount_rate, minimum_charge] بعد التطبيق.
     * الحد الأدنى يُجلب من جدول minimum_charge_rules في قاعدة البيانات (مع كاش).
     *
     * @param  Subscriber          $subscriber
     * @param  float               $currentDiscount
     * @param  float               $currentMinCharge
     * @param  \Carbon\Carbon|null $date  تاريخ لجلب نسبة الخصم (افتراضي: اليوم)
     */
    public static function applySubscriberRules(Subscriber $subscriber, float $currentDiscount = 0, float $currentMinCharge = 0, ?\Carbon\Carbon $date = null): array
    {
        // خصم موظف الشركة تلقائياً (يُمنح فقط إذا: موظف + منزلي + 1 فاز)
        $discount = $subscriber->isEligibleForEmployeeDiscount()
            ? self::getEmployeeDiscountRate($date)
            : $currentDiscount;

        // الحد الأدنى من قاعدة البيانات حسب أمبير/فاز الاشتراك
        $ampere = intval($subscriber->ampere ?? 0);
        $phase  = $subscriber->phase_type ?? 1;
        $dbMin  = MinimumChargeRule::getMinCharge($ampere, $phase);
        // استخدام القيمة من DB إذا وُجدت، وإلا الاحتفاظ بالقيمة الحالية
        $minCharge = $dbMin > 0 ? $dbMin : $currentMinCharge;

        return [$discount, $minCharge];
    }

    /**
     * احتساب قيم الفاتورة وفق آلية الاحتساب المعتمدة:
     *   ثمن الاستهلاك = kwh × price × (1 - discount/100)
     *   قيمة الفاتورة = max(ثمن الاستهلاك, الحد الأدنى)
     *   المبلغ المطلوب = max(قيمة_الفاتورة + رصيد_سابق, 0)
     *
     * @return array{consumption_cost: float, invoice_amount: float, total_amount: float}
     */
    public static function calculateAmounts(
        float $consumptionKwh,
        float $pricePerKwh,
        float $discountRate,
        float $minimumCharge,
        float $previousBalance = 0.0
    ): array {
        // الخطوة 1: ثمن الاستهلاك (قبل تطبيق الحد الأدنى)
        $consumptionCost = $consumptionKwh * $pricePerKwh * (1 - ($discountRate / 100));

        // الخطوة 2: قيمة الفاتورة = max(ثمن الاستهلاك, الحد الأدنى)
        $invoiceAmount = max($consumptionCost, $minimumCharge);

        // الخطوة 3: المبلغ المطلوب = قيمة_الفاتورة + رصيد_سابق
        // الرصيد موجب = مديون على المشترك (يُضاف)
        // الرصيد سالب = دائن لصالح المشترك (يُخصم)
        // في حال كان الناتج سالباً يُثبَّت على صفر
        $rawTotal    = $invoiceAmount + $previousBalance;
        $totalAmount = max($rawTotal, 0.0);

        return [
            'consumption_cost' => round($consumptionCost, 2),
            'invoice_amount'   => round($invoiceAmount,   2),
            'total_amount'     => round($totalAmount,     2),
        ];
    }

    /**
     * للتوافق مع الكود القديم - إرجاع قيمة الفاتورة فقط
     */
    public static function calculateAmount(
        float $consumptionKwh,
        float $pricePerKwh,
        float $discountRate,
        float $minimumCharge
    ): float {
        return self::calculateAmounts($consumptionKwh, $pricePerKwh, $discountRate, $minimumCharge)['invoice_amount'];
    }

    /**
     * إنشاء فاتورة مسودة تلقائياً من قراءة عداد معتمدة.
     * يُستخدم عند الاعتماد التلقائي والترحيل للفوترة.
     */
    public static function createFromReading(MeterReading $reading, int $createdBy): self
    {
        $subscriber    = $reading->subscriber;
        $activeTariff  = ElectricityTariffPrice::getActivePriceForDate($reading->reading_date);
        $pricePerKwh   = $activeTariff ? (float) $activeTariff->price_per_kwh : 0;

        // تطبيق قواعد الخصم والحد الأدنى حسب بيانات المشترك (بتاريخ القراءة)
        [$discountRate, $minimumCharge] = self::applySubscriberRules($subscriber, 0, 0, $reading->reading_date);

        // الرصيد السابق التراكمي: مجموع invoice_amount − مجموع الدفعات
        $previousBalance = self::calculatePreviousBalance($subscriber->id);

        $amounts = self::calculateAmounts(
            (float) $reading->consumption_kwh,
            $pricePerKwh,
            $discountRate,
            $minimumCharge,
            $previousBalance
        );

        return self::create([
            'invoice_number'          => null, // يُنشأ عند الإصدار النهائي
            'subscriber_id'           => $subscriber->id,
            'meter_reading_id'        => $reading->id,
            'invoice_date'            => $reading->reading_date,
            'consumption_period_days' => $reading->consumption_period_days,
            'consumption_kwh'         => $reading->consumption_kwh,
            'price_per_kwh'           => $pricePerKwh,
            'discount_rate'           => $discountRate,
            'invoice_amount'          => $amounts['invoice_amount'],
            'minimum_charge'          => $minimumCharge,
            'previous_balance'        => round($previousBalance, 2),
            'total_amount'            => $amounts['total_amount'],
            'invoice_status'          => self::STATUS_DRAFT,
            'created_by'              => $createdBy,
        ]);
    }
}
