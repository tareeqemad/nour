<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TracksUser;

class OperatorTerritory extends Model
{
    use SoftDeletes, TracksUser;

    protected $table = 'operator_territories';

    protected $fillable = [
        'operator_id',
        'center_latitude',
        'center_longitude',
        'radius_km',
        'name',
        'description',
        'created_by',
        'last_updated_by',
    ];

    protected function casts(): array
    {
        return [
            'center_latitude' => 'decimal:8',
            'center_longitude' => 'decimal:8',
            'radius_km' => 'decimal:2',
        ];
    }

    /**
     * علاقة مع المشغل
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    /**
     * حساب المسافة بين نقطتين باستخدام صيغة Haversine
     * 
     * @param float $lat1 خط عرض النقطة الأولى
     * @param float $lon1 خط طول النقطة الأولى
     * @param float $lat2 خط عرض النقطة الثانية
     * @param float $lon2 خط طول النقطة الثانية
     * @return float المسافة بالكيلومترات
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // نصف قطر الأرض بالكيلومترات
        $earthRadius = 6371;

        // تحويل الدرجات إلى راديان
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        // صيغة Haversine
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * التحقق من أن النقطة (latitude, longitude) تقع ضمن هذه المنطقة
     * 
     * @param float $latitude خط عرض النقطة
     * @param float $longitude خط طول النقطة
     * @return bool
     */
    public function containsPoint(float $latitude, float $longitude): bool
    {
        $distance = self::calculateDistance(
            $this->center_latitude,
            $this->center_longitude,
            $latitude,
            $longitude
        );

        return $distance <= $this->radius_km;
    }

    /**
     * البحث عن منطقة لمشغل آخر تحتوي على النقطة المحددة
     * 
     * @param float $latitude خط عرض النقطة
     * @param float $longitude خط طول النقطة
     * @param int|null $excludeOperatorId استثناء مشغل معين (مثل المشغل الحالي)
     * @return OperatorTerritory|null
     */
    public static function findTerritoryContainingPoint(float $latitude, float $longitude, ?int $excludeOperatorId = null): ?self
    {
        $territories = self::with('operator')
            ->where('operator_id', '!=', $excludeOperatorId)
            ->get();

        foreach ($territories as $territory) {
            if ($territory->containsPoint($latitude, $longitude)) {
                return $territory;
            }
        }

        return null;
    }

    /**
     * إنشاء منطقة جديدة للمشغل بناءً على موقع وحدة التوليد
     * 
     * @param int $operatorId معرف المشغل
     * @param float $latitude خط عرض المركز
     * @param float $longitude خط طول المركز
     * @param float|null $radiusKm نصف القطر بالكيلومترات (إذا لم يتم تحديده، سيتم استخدام قيمة المشغل)
     * @param string|null $name اسم المنطقة
     * @return self
     */
    public static function createForOperator(int $operatorId, float $latitude, float $longitude, ?float $radiusKm = null, ?string $name = null): self
    {
        // إذا لم يتم تحديد نصف القطر، جلب القيمة من المشغل
        if ($radiusKm === null) {
            $operator = Operator::find($operatorId);
            $radiusKm = $operator && $operator->territory_radius_km 
                ? (float) $operator->territory_radius_km 
                : 5.0; // افتراضي 5 كم إذا لم يكن محدد
        }

        return self::create([
            'operator_id' => $operatorId,
            'center_latitude' => $latitude,
            'center_longitude' => $longitude,
            'radius_km' => $radiusKm,
            'name' => $name ?? "منطقة المشغل #{$operatorId}",
            'created_by' => auth()->id(),
        ]);
    }
}
