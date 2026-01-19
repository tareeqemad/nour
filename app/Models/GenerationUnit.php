<?php

namespace App\Models;

use App\Governorate;
use App\Models\ConstantDetail;
use App\Models\Task;
use App\Models\FuelEfficiency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class GenerationUnit extends Model
{
    use SoftDeletes, TracksUser;

    protected $table = 'generation_units';

    protected $fillable = [
        'operator_id',
        'unit_code',
        'unit_number',
        'name',
        'generators_count',
        'status_id', // تم تغييره من status
        'created_by',
        'last_updated_by',
        // الملكية والتشغيل
        'owner_name',
        'owner_id_number',
        'operation_entity_id', // تم تغييره من operation_entity
        'operator_id_number',
        'operator_name', // اسم المشغل (يستخدم في حالة "طرف آخر")
        'phone',
        'phone_alt',
        'email',
        // الموقع
        'governorate_id', // ID من constant_details - ثابت Master رقم 1 (المحافظات)
        'city_id',
        'detailed_address',
        'latitude',
        'longitude',
        // القدرات الفنية
        'total_capacity',
        'synchronization_available_id', // تم تغييره من synchronization_available
        'max_synchronization_capacity',
        // المستفيدون والبيئة
        'beneficiaries_count',
        'beneficiaries_description',
        'environmental_compliance_status_id', // تم تغييره من environmental_compliance_status
        'qr_code_generated_at', // تاريخ توليد QR Code
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'total_capacity' => 'decimal:2',
            'max_synchronization_capacity' => 'decimal:2',
            'beneficiaries_count' => 'integer',
            'qr_code_generated_at' => 'datetime',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function generators(): HasMany
    {
        return $this->hasMany(Generator::class);
    }

    public function fuelTanks(): HasMany
    {
        return $this->hasMany(FuelTank::class)->orderBy('order');
    }

    /**
     * علاقة مع ثابت المحافظة (governorate_id)
     * يستخدم: ConstantsHelper::get(1) - ثابت Master رقم 1
     */
    public function governorateDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'governorate_id');
    }

    /**
     * علاقة مع ثابت المدينة (city_id)
     * يستخدم: ConstantsHelper::get(20) - ثابت Master رقم 20
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ConstantDetail::class, 'city_id');
    }

    /**
     * علاقة مع ثابت المدينة (city_id) - اسم بديل للتوافق
     */
    public function cityDetail(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ConstantDetail::class, 'city_id');
    }

    /**
     * علاقة مع ثابت حالة الوحدة (status_id)
     * يستخدم: ConstantsHelper::get(15) - ثابت Master رقم 15
     */
    public function statusDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'status_id');
    }

    /**
     * علاقة مع ثابت جهة التشغيل (operation_entity_id)
     * يستخدم: ConstantsHelper::get(2) - ثابت Master رقم 2
     */
    public function operationEntityDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'operation_entity_id');
    }

    /**
     * علاقة مع ثابت إمكانية المزامنة (synchronization_available_id)
     * يستخدم: ConstantsHelper::get(16) - ثابت Master رقم 16
     */
    public function synchronizationAvailableDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'synchronization_available_id');
    }

    /**
     * علاقة مع ثابت حالة الامتثال البيئي (environmental_compliance_status_id)
     * يستخدم: ConstantsHelper::get(14) - ثابت Master رقم 14
     */
    public function environmentalComplianceStatusDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'environmental_compliance_status_id');
    }

    /**
     * الحصول على اسم المشغل من العلاقة
     */
    public function getOperatorNameAttribute(): ?string
    {
        return $this->operator?->name;
    }

    /**
     * الحصول على اسم المدينة من الثوابت
     */
    public function getCityName(): ?string
    {
        return $this->city?->label;
    }

    /**
     * توليد رقم الوحدة التالي (001, 002, إلخ) حسب المحافظة والمدينة
     * الرقم التسلسلي فريد ضمن نفس المحافظة والمدينة
     */
    public static function getNextUnitNumber(int $operatorId): string
    {
        $operator = Operator::find($operatorId);
        if (!$operator) {
            return '001';
        }

        $governorate = $operator->governorate;
        $cityId = $operator->city_id;

        if (!$governorate || !$cityId) {
            return '001';
        }

        // الحصول على ترميز المحافظة والمدينة
        $governorateCode = $governorate->code();
        $cityDetail = ConstantDetail::find($cityId);
        if (!$cityDetail || !$cityDetail->code) {
            return '001';
        }
        $cityCode = $cityDetail->code;

        // البحث عن آخر رقم وحدة في نفس المحافظة والمدينة
        // من خلال البحث في كود الوحدة بالصيغة GU-PP-CC-NNN
        $prefix = "GU-{$governorateCode}-{$cityCode}-";
        
        $lastUnit = static::where('unit_code', 'like', $prefix . '%')
            ->whereNotNull('unit_code')
            ->get()
            ->map(function ($unit) use ($prefix) {
                // استخراج الرقم التسلسلي من unit_code
                $code = $unit->unit_code;
                if (str_starts_with($code, $prefix)) {
                    $numberPart = substr($code, strlen($prefix));
                    return (int) $numberPart;
                }
                return 0;
            })
            ->filter(fn($num) => $num > 0)
            ->sortDesc()
            ->first();

        if ($lastUnit) {
            $nextNumber = $lastUnit + 1;
        } else {
            $nextNumber = 1;
        }

        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * توليد رقم الوحدة التالي بناءً على governorate code و city code مباشرة
     */
    public static function getNextUnitNumberByLocation(string $governorateCode, string $cityCode): string
    {
        $prefix = "GU-{$governorateCode}-{$cityCode}-";
        
        $lastUnit = static::where('unit_code', 'like', $prefix . '%')
            ->whereNotNull('unit_code')
            ->get()
            ->map(function ($unit) use ($prefix) {
                $code = $unit->unit_code;
                if (str_starts_with($code, $prefix)) {
                    $numberPart = substr($code, strlen($prefix));
                    return (int) $numberPart;
                }
                return 0;
            })
            ->filter(fn($num) => $num > 0)
            ->sortDesc()
            ->first();

        if ($lastUnit) {
            $nextNumber = $lastUnit + 1;
        } else {
            $nextNumber = 1;
        }

        return str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * توليد كود الوحدة بالصيغة GU-PP-CC-NNN
     * حيث:
     * GU: ثابت (GENERATION UNIT)
     * PP: ترميز المحافظة (2 أحرف: NG, GZ, MD, KH, RF)
     * CC: ترميز المدينة (2-3 أحرف: BH, BL, JB, GZ, DB, NS, BR, MG, ZW, KH, QR, AK, AS, KZ, RF, SH, NS)
     * NNN: رقم تسلسلي للوحدة (3 أرقام: 001, 002, إلخ) - فريد ضمن نفس المحافظة والمدينة
     */
    public static function generateUnitCode(int $operatorId, ?string $unitNumber = null, ?string $governorateCode = null, ?string $cityCode = null): ?string
    {
        // إذا تم تمرير governorateCode و cityCode مباشرة، استخدمهما
        if ($governorateCode && $cityCode) {
            if (!$unitNumber) {
                $unitNumber = self::getNextUnitNumberByLocation($governorateCode, $cityCode);
            }
            return "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";
        }

        // الطريقة القديمة (للتوافق مع الكود القديم - لكن لن تعمل لأن Operator لم يعد لديه governorate و city_id)
        // هذه الدالة موجودة للتوافق فقط، لكن الأفضل استخدام getNextUnitNumberByLocation وبناء الكود يدوياً
        return null;
    }

    /**
     * توليد كود الوحدة باستخدام كود المحافظة وكود المدينة مباشرة
     * الصيغة: GU-PP-CC-NNN
     */
    public static function generateUnitCodeByLocation(string $governorateCode, string $cityCode, ?string $unitNumber = null): string
    {
        if (!$unitNumber) {
            $unitNumber = self::getNextUnitNumberByLocation($governorateCode, $cityCode);
        }
        return "GU-{$governorateCode}-{$cityCode}-{$unitNumber}";
    }

    /**
     * التحقق من أن عدد المولدات الفعلي يطابق العدد المطلوب
     */
    public function hasCorrectGeneratorCount(): bool
    {
        $actualCount = $this->generators()->count();
        return $actualCount === $this->generators_count;
    }

    /**
     * الحصول على عدد المولدات المتبقية لإكمال العدد المطلوب
     */
    public function getRemainingGeneratorsCount(): int
    {
        $actualCount = $this->generators()->count();
        return max(0, $this->generators_count - $actualCount);
    }

    /**
     * التحقق من إمكانية حذف وحدة التوليد
     * لا يمكن حذفها إذا كان فيها مولدات (لأن المولدات قد تحتوي على سجلات)
     * 
     * @return array ['can_delete' => bool, 'related_records' => array, 'generators_with_records' => array]
     */
    public function canBeDeleted(): array
    {
        $relatedRecords = [];
        $generatorsWithRecords = [];

        // التحقق من وجود مولدات
        $generatorsCount = $this->generators()->count();
        if ($generatorsCount > 0) {
            $relatedRecords[] = [
                'type' => 'generators',
                'label' => 'مولدات',
                'count' => $generatorsCount,
            ];

            // التحقق من كل مولد إذا كان يمكن حذفه
            foreach ($this->generators as $generator) {
                $generatorDeletionCheck = $generator->canBeDeleted();
                if (!$generatorDeletionCheck['can_delete']) {
                    $generatorsWithRecords[] = [
                        'id' => $generator->id,
                        'name' => $generator->name,
                        'generator_number' => $generator->generator_number,
                        'related_records' => $generatorDeletionCheck['related_records'],
                    ];
                }
            }
        }

        // التحقق من خزانات الوقود
        $fuelTanksCount = $this->fuelTanks()->count();
        if ($fuelTanksCount > 0) {
            $relatedRecords[] = [
                'type' => 'fuel_tanks',
                'label' => 'خزانات وقود',
                'count' => $fuelTanksCount,
            ];
        }

        // التحقق من سجلات كفاءة الوقود المرتبطة مباشرة بالوحدة
        $fuelEfficienciesCount = FuelEfficiency::where('generation_unit_id', $this->id)->count();
        if ($fuelEfficienciesCount > 0) {
            $relatedRecords[] = [
                'type' => 'fuel_efficiencies',
                'label' => 'سجلات كفاءة وقود',
                'count' => $fuelEfficienciesCount,
            ];
        }

        // التحقق من المهام المرتبطة بالوحدة
        $tasksCount = Task::where('generation_unit_id', $this->id)->count();
        if ($tasksCount > 0) {
            $relatedRecords[] = [
                'type' => 'tasks',
                'label' => 'مهام',
                'count' => $tasksCount,
            ];
        }

        return [
            'can_delete' => empty($relatedRecords),
            'related_records' => $relatedRecords,
            'generators_with_records' => $generatorsWithRecords,
        ];
    }
}

