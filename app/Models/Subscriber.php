<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;
use App\Helpers\ConstantsHelper;

class Subscriber extends Model
{
    use HasFactory, SoftDeletes, TracksUser;

    protected $table = 'subscribers';

    protected $fillable = [
        'subscription_number',
        'subscriber_id_number',
        'subscriber_name',
        'phone',
        'governorate_name',
        'address',
        'subscription_date',
        'subscription_category',
        'phase_type',
        'subscription_status',
        'meter_number',
        'ampere',
        'opening_reading',
        'service_type',
        'is_employee_subscription',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'subscription_date' => 'date',
            'subscription_category' => 'integer',
            'phase_type' => 'integer',
            'subscription_status' => 'integer',
            'service_type' => 'integer',
            'ampere' => 'decimal:2',
            'opening_reading' => 'decimal:2',
            'is_employee_subscription' => 'boolean',
        ];
    }

    /**
     * العلاقة مع وحدات التوليد (Many-to-Many)
     */
    public function generationUnits(): BelongsToMany
    {
        return $this->belongsToMany(GenerationUnit::class, 'subscriber_generation_unit')
            ->withTimestamps();
    }

    /**
     * العلاقة مع قراءات العدادات
     */
    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    /**
     * العلاقة مع الفواتير
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ السجل
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع المستخدم الذي حدث السجل
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    // ===== ثوابت تصنيف الاشتراك =====
    const CATEGORY_RESIDENTIAL = 1; // منزلي
    const CATEGORY_COMMERCIAL  = 2; // تجاري
    const CATEGORY_SERVICE     = 3; // خدماتي
    const CATEGORY_INDUSTRIAL  = 4; // صناعي

    // ===== ثوابت نوع الفاز =====
    const PHASE_SINGLE = 1; // 1 فاز
    const PHASE_THREE  = 2; // 3 فاز

    /**
     * هل يستحق المشترك خصم موظف شركة؟
     *
     * الشروط الثلاثة مجتمعة:
     *   1. مُعلَّم كموظف شركة (is_employee_subscription)
     *   2. تصنيف الاشتراك: منزلي (subscription_category = 1)
     *   3. نوع الفاز: 1 فاز (phase_type = 1)
     */
    public function isEligibleForEmployeeDiscount(): bool
    {
        return (bool) $this->is_employee_subscription
            && (int) $this->subscription_category === self::CATEGORY_RESIDENTIAL
            && (int) $this->phase_type === self::PHASE_SINGLE;
    }

    /**
     * Accessor للحصول على اسم تصنيف الاشتراك (يقرأ من الثوابت - ثابت رقم 23)
     */
    public function getSubscriptionCategoryNameAttribute(): string
    {
        $details = ConstantsHelper::get(23);
        $match   = $details->firstWhere('value', (string) $this->subscription_category);
        if ($match) {
            return $match->label;
        }
        // fallback ثابت في حال لم تُحمَّل الثوابت بعد
        return match((int) $this->subscription_category) {
            1 => 'منزلي', 2 => 'تجاري', 3 => 'خدماتي', 4 => 'صناعي',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor للحصول على اسم نوع الفاز (يقرأ من الثوابت - ثابت رقم 24)
     */
    public function getPhaseTypeNameAttribute(): string
    {
        $details = ConstantsHelper::get(24);
        $match   = $details->firstWhere('value', (string) $this->phase_type);
        if ($match) {
            return $match->label;
        }
        return match((int) $this->phase_type) {
            1 => '1 فاز', 2 => '3 فاز',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor للحصول على اسم حالة الاشتراك (يقرأ من الثوابت - ثابت رقم 25)
     */
    public function getSubscriptionStatusNameAttribute(): string
    {
        $details = ConstantsHelper::get(25);
        $match   = $details->firstWhere('value', (string) $this->subscription_status);
        if ($match) {
            return $match->label;
        }
        return match((int) $this->subscription_status) {
            1 => 'نشط', 2 => 'موقوف', 3 => 'مغلق',
            default => 'غير محدد',
        };
    }

    /**
     * Accessor للحصول على اسم نوع الخدمة (يقرأ من الثوابت - ثابت رقم 26)
     */
    public function getServiceTypeNameAttribute(): string
    {
        $details = ConstantsHelper::get(26);
        $match   = $details->firstWhere('value', (string) $this->service_type);
        if ($match) {
            return $match->label;
        }
        return match((int) $this->service_type) {
            1 => 'مولد', 2 => 'شمسي', 3 => 'هجين',
            default => 'غير محدد',
        };
    }

    /**
     * توليد رقم اشتراك تلقائي بتنسيق GG-OO-P-SSSS
     * 
     * @param int $generationUnitId معرف وحدة التوليد
     * @param int $phaseType نوع الفاز (1 أو 2 للنظام، لكن يُحول إلى 1 أو 3 في رقم الاشتراك)
     * @return string رقم الاشتراك المولد
     */
    public static function generateSubscriptionNumber(int $generationUnitId, int $phaseType): string
    {
        // جلب بيانات وحدة التوليد مع المحافظة
        $generationUnit = \App\Models\GenerationUnit::with(['governorateDetail'])
            ->find($generationUnitId);
            
        if (!$generationUnit) {
            throw new \Exception('وحدة التوليد غير موجودة');
        }

        // استخراج رقم وحدة التوليد (GG) - آخر رقمين من unit_number أو ID
        $unitNumber = $generationUnit->unit_number 
            ? str_pad((int) substr($generationUnit->unit_number, -2), 2, '0', STR_PAD_LEFT)
            : str_pad($generationUnit->id % 100, 2, '0', STR_PAD_LEFT);

        // استخراج كود المحافظة (OO)
        $governorateCode = '00'; // قيمة افتراضية
        if ($generationUnit->governorateDetail && $generationUnit->governorateDetail->value) {
            $governorateCode = str_pad($generationUnit->governorateDetail->value, 2, '0', STR_PAD_LEFT);
        }

        // تحويل نوع الفاز من نظام قاعدة البيانات (1,2) إلى رقم الاشتراك (1,3)
        $phaseCode = $phaseType == 2 ? '3' : '1';

        // بناء بادئة رقم الاشتراك
        $prefix = "{$unitNumber}{$governorateCode}{$phaseCode}";

        // البحث عن آخر رقم تسلسلي لهذه التوليفة
        $lastSubscriber = self::where('subscription_number', 'LIKE', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(subscription_number, -4) AS UNSIGNED) DESC')
            ->first();

        $nextSequence = 1;
        if ($lastSubscriber) {
            $lastSequence = (int) substr($lastSubscriber->subscription_number, -4);
            $nextSequence = $lastSequence + 1;
        }

        // التحقق من عدم تجاوز الحد الأقصى
        if ($nextSequence > 9999) {
            throw new \Exception('تم الوصول للحد الأقصى من أرقام الاشتراك لهذه التوليفة');
        }

        // تنسيق الرقم التسلسلي (SSSS)
        $sequenceCode = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$sequenceCode}";
    }

    /**
     * التحقق من وجود رقم اشتراك
     */
    public static function subscriptionNumberExists(string $subscriptionNumber): bool
    {
        return self::where('subscription_number', $subscriptionNumber)->exists();
    }

    /**
     * الحصول على إحصائيات أرقام الاشتراك لتوليفة معينة
     */
    public static function getSubscriptionStats(int $generationUnitId, int $phaseType): array
    {
        $generationUnit = \App\Models\GenerationUnit::with(['governorateDetail'])
            ->find($generationUnitId);
            
        if (!$generationUnit) {
            return ['total' => 0, 'available' => 0, 'used' => 0];
        }

        $unitNumber = $generationUnit->unit_number 
            ? str_pad((int) substr($generationUnit->unit_number, -2), 2, '0', STR_PAD_LEFT)
            : str_pad($generationUnit->id % 100, 2, '0', STR_PAD_LEFT);

        $governorateCode = '00';
        if ($generationUnit->governorateDetail && $generationUnit->governorateDetail->value) {
            $governorateCode = str_pad($generationUnit->governorateDetail->value, 2, '0', STR_PAD_LEFT);
        }

        $phaseCode = $phaseType == 2 ? '3' : '1';
        $prefix = "{$unitNumber}{$governorateCode}{$phaseCode}";

        $used = self::where('subscription_number', 'LIKE', "{$prefix}%")->count();
        $total = 9999;
        $available = $total - $used;

        return [
            'total' => $total,
            'used' => $used,
            'available' => $available,
            'prefix' => $prefix
        ];
    }
}
