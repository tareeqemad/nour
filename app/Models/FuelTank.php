<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ConstantDetail;

class FuelTank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generation_unit_id',
        'tank_code',
        'capacity',
        'location_id', // ID من constant_details - ثابت Master رقم 21 (موقع الخزان)
        'filtration_system_available',
        'condition_id', // ID من constant_details - ثابت Master رقم 22 (حالة الخزان)
        'material_id', // ID من constant_details - ثابت Master رقم 10 (مادة التصنيع)
        'usage_id', // ID من constant_details - ثابت Master رقم 11 (الاستخدام)
        'measurement_method_id', // ID من constant_details - ثابت Master رقم 19 (طريقة القياس)
        'order',
    ];

    protected function casts(): array
    {
        return [
            'filtration_system_available' => 'boolean',
        ];
    }

    public function generationUnit(): BelongsTo
    {
        return $this->belongsTo(GenerationUnit::class);
    }

    // Relationships للثوابت - ثابت Master رقم 21 (موقع الخزان)
    public function locationDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'location_id');
    }

    // Relationships للثوابت - ثابت Master رقم 10 (مادة التصنيع)
    public function materialDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'material_id');
    }

    // Relationships للثوابت - ثابت Master رقم 11 (الاستخدام)
    public function usageDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'usage_id');
    }

    // Relationships للثوابت - ثابت Master رقم 19 (طريقة القياس)
    public function measurementMethodDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'measurement_method_id');
    }

    // Relationships للثوابت - ثابت Master رقم 22 (حالة الخزان)
    public function conditionDetail(): BelongsTo
    {
        return $this->belongsTo(ConstantDetail::class, 'condition_id');
    }

    /**
     * توليد كود خزان الوقود التالي بناءً على unit_code لوحدة التوليد
     * الصيغة الكاملة: GU-PP-CC-NNN-TXX
     * حيث:
     * - GU-PP-CC-NNN: كود وحدة التوليد (Generation Unit Code)
     * - TXX: رقم خزان الوقود داخل الوحدة (T01 - T99)
     * 
     * مثال: GU-MD-DR-001-T01, GU-MD-DR-001-T02
     * 
     * @param int|null $generationUnitId معرف وحدة التوليد
     * @return string|null كود خزان الوقود أو null في حالة الفشل
     */
    public static function getNextTankCode(?int $generationUnitId = null): ?string
    {
        if (!$generationUnitId) {
            return null;
        }

        $generationUnit = GenerationUnit::find($generationUnitId);
        if (!$generationUnit || !$generationUnit->unit_code) {
            return null;
        }

        $unitCode = $generationUnit->unit_code;
        $prefix = $unitCode . '-T';

        // البحث عن آخر رقم خزان في نفس وحدة التوليد
        $lastTank = static::where('generation_unit_id', $generationUnitId)
            ->whereNotNull('tank_code')
            ->where('tank_code', 'like', $prefix . '%')
            ->get()
            ->map(function ($tank) use ($prefix) {
                // استخراج الرقم من tank_code
                $numberPart = substr($tank->tank_code, strlen($prefix));
                return (int) $numberPart;
            })
            ->filter()
            ->max();

        if ($lastTank !== null) {
            $nextNumber = $lastTank + 1;
        } else {
            $nextNumber = 1;
        }

        // التأكد من أن الرقم لا يتجاوز 99
        if ($nextNumber > 99) {
            return null; // تم الوصول إلى الحد الأقصى
        }

        return $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }
}
