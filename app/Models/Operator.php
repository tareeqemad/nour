<?php

namespace App\Models;

use App\Governorate;
use App\Models\ConstantDetail;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class Operator extends Model
{
    use SoftDeletes, TracksUser;

    protected $table = 'operators';

    protected $fillable = [
        // البيانات الأساسية للمشغل
        'name', // اسم المشغل بالعربي
        
        // العلاقة مع المستخدم (المالك)
        'owner_id', // ID المستخدم الذي يملك هذا المشغل
        
        // بيانات المالك والمشغل
        'owner_name', // اسم المالك الفعلي
        'owner_id_number', // رقم هوية المالك
        'operator_id_number', // رقم هوية المشغل
        
        // الحالة
        'status', // active/inactive
        'is_approved', // معتمد/غير معتمد
        'profile_completed', // اكتمال الملف الشخصي
    ];

    protected function casts(): array
    {
        return [
            'profile_completed' => 'boolean',
            'is_approved' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'operator_user')
            ->withTimestamps();
    }

    /**
     * Users with custom roles linked to this operator
     * Custom roles are defined dynamically by Energy Authority or Company Owner
     */
    public function staff(): BelongsToMany
    {
        return $this->users()->whereHas('roleModel', function($q) {
            $q->where('is_system', false)
              ->where('operator_id', $this->id);
        });
    }

    /**
     * وحدات التوليد التابعة لهذا المشغل
     */
    public function generationUnits(): HasMany
    {
        return $this->hasMany(GenerationUnit::class);
    }

    /**
     * المولدات التابعة لهذا المشغل (عبر وحدات التوليد)
     */
    public function generators(): HasManyThrough
    {
        return $this->hasManyThrough(Generator::class, GenerationUnit::class);
    }

    public function operationLogs(): HasMany
    {
        return $this->hasMany(OperationLog::class);
    }

    public function complianceSafeties(): HasMany
    {
        return $this->hasMany(ComplianceSafety::class);
    }

    public function electricityTariffPrices(): HasMany
    {
        return $this->hasMany(ElectricityTariffPrice::class);
    }

    /**
     * علاقة مع ثابت المدينة (city_id)
     * يستخدم: ConstantsHelper::get(20) - ثابت Master رقم 20
     */
    public function cityDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'city_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function isProfileComplete(): bool
    {
        // يجب إدخال الحقول الأربعة: name, owner_name, owner_id_number, operator_id_number
        return $this->profile_completed 
            && !empty($this->name) 
            && !empty($this->owner_name) 
            && !empty($this->owner_id_number) 
            && !empty($this->operator_id_number);
    }

    /**
     * التحقق من أن المشغل معتمد ومفعل
     */
    public function isApproved(): bool
    {
        return $this->status === 'active' && $this->is_approved === true;
    }

    public function getGovernorateDetails(): ?array
    {
        return $this->governorate?->details();
    }

    public function getGovernorateLabel(): ?string
    {
        return $this->governorate?->label();
    }

    public function getGovernorateCode(): ?string
    {
        return $this->governorate?->code();
    }

    /**
     * توليد رقم الوحدة التالي (001, 002, إلخ) حسب المحافظة والمدينة
     */
    public static function getNextUnitNumber(?Governorate $governorate, ?int $cityId = null): string
    {
        if (!$governorate || !$cityId) {
            return '001';
        }

        // البحث عن آخر رقم وحدة في نفس المحافظة والمدينة
        $lastUnit = static::where('governorate', $governorate)
            ->where('city_id', $cityId)
            ->whereNotNull('unit_number')
            ->orderByRaw('CAST(unit_number AS UNSIGNED) DESC')
            ->first();

        if ($lastUnit && $lastUnit->unit_number) {
            $lastNumber = (int) $lastUnit->unit_number;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * توليد كود الوحدة بالصيغة GU-PP-CC-NNN
     * حيث:
     * GU: ثابت (GENERATION UNIT)
     * PP: ترميز المحافظة
     * CC: ترميز المدينة
     * NNN: رقم الوحدة (001, 002, إلخ)
     */
    public static function generateUnitCode(?Governorate $governorate, ?int $cityId, ?string $unitNumber = null): ?string
    {
        if (!$governorate || !$cityId) {
            return null;
        }

        // الحصول على ترميز المحافظة
        $governorateCode = $governorate->code();
        
        // الحصول على ترميز المدينة من الثوابت
        $cityDetail = ConstantDetail::find($cityId);
        if (!$cityDetail || !$cityDetail->code) {
            return null;
        }
        
        $cityCode = $cityDetail->code;
        
        // استخدام رقم الوحدة الموجود أو توليد واحد جديد
        if (!$unitNumber) {
            $unitNumber = self::getNextUnitNumber($governorate, $cityId);
        }

        return "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";
    }

    public function getMissingFields(): array
    {
        $missing = [];

        if (empty($this->name)) $missing[] = 'اسم المشغل';
        if (empty($this->owner_name)) $missing[] = 'اسم المالك';
        if (empty($this->owner_id_number)) $missing[] = 'رقم هوية المالك';
        if (empty($this->operator_id_number)) $missing[] = 'رقم هوية المشغل';

        return $missing;
    }

    /**
     * الحصول على اسم المدينة من الثوابت
     */
    public function getCityName(): ?string
    {
        return $this->cityDetail?->label;
    }

    /**
     * Accessor للحصول على رقم الهاتف من المالك
     */
    public function getPhoneAttribute(): ?string
    {
        return $this->owner?->phone;
    }

    /**
     * Boot the model and register event listeners
     */
    protected static function boot(): void
    {
        parent::boot();

        // When a new operator is created and is not approved, send notification to all approvers
        static::created(function (Operator $operator) {
            // إنشاء أدوار مخصصة تلقائياً للمشغل الجديد
            self::createDefaultRolesForOperator($operator);
            
            // إذا كان الملف مكتملاً عند الإنشاء، أرسل إشعار اكتمال الملف
            if ($operator->profile_completed && $operator->name) {
                \App\Models\Notification::notifyOperatorApprovers(
                    'operator_profile_completed',
                    'اكتمال ملف المشغل',
                    "تم اكتمال ملف المشغل ({$operator->name}). المالك: " . ($operator->owner?->name ?? 'غير محدد'),
                    route('admin.operators.show', $operator)
                );
            }
            
            if (!$operator->is_approved && $operator->owner) {
                // Send notification to all users who can approve operators (Super Admin, Admin, Energy Authority)
                \App\Models\Notification::notifyOperatorApprovers(
                    'operator_pending_approval',
                    'مشغل جديد يحتاج للاعتماد',
                    "تم إنشاء مشغل جديد ({$operator->name}) يحتاج للاعتماد والتفعيل. المالك: {$operator->owner->name}",
                    route('admin.operators.show', $operator)
                );
            }
        });

        // When profile_completed changes from false to true, send notification to approvers
        static::updating(function (Operator $operator) {
            if ($operator->isDirty('profile_completed') 
                && $operator->getOriginal('profile_completed') === false 
                && $operator->profile_completed === true) {
                
                // إرسال إشعار لسلطة الطاقة والسوبر أدمن والأدمن أن المشغل أكمل ملفه
                \App\Models\Notification::notifyOperatorApprovers(
                    'operator_profile_completed',
                    'اكتمال ملف المشغل',
                    "تم اكتمال ملف المشغل ({$operator->name}). المالك: " . ($operator->owner?->name ?? 'غير محدد'),
                    route('admin.operators.show', $operator)
                );
            }
        });
    }
    
    /**
     * إنشاء أدوار مخصصة افتراضية للمشغل الجديد
     * يتم إنشاء دورين: فني ومحاسب
     */
    private static function createDefaultRolesForOperator(Operator $operator): void
    {
        try {
            // 1. دور فني المشغل
            Role::create([
                'name' => 'technician_' . $operator->id,
                'label' => 'فني مشغل',
                'description' => 'دور فني المشغل - تم إنشاؤه تلقائياً للمشغل: ' . $operator->name,
                'is_system' => false,
                'operator_id' => $operator->id,
                'created_by' => $operator->owner_id, // ربط الدور بالمشغل (المالك)
                'order' => 100,
            ]);
            
            // 2. دور المحاسب
            Role::create([
                'name' => 'accountant_' . $operator->id,
                'label' => 'محاسب',
                'description' => 'دور المحاسب - تم إنشاؤه تلقائياً للمشغل: ' . $operator->name,
                'is_system' => false,
                'operator_id' => $operator->id,
                'created_by' => $operator->owner_id, // ربط الدور بالمشغل (المالك)
                'order' => 101,
            ]);
            
            \Log::info("تم إنشاء أدوار افتراضية للمشغل: {$operator->name} (ID: {$operator->id})");
        } catch (\Exception $e) {
            \Log::error("فشل إنشاء أدوار افتراضية للمشغل: {$operator->name} (ID: {$operator->id}). الخطأ: " . $e->getMessage());
        }
    }
}
