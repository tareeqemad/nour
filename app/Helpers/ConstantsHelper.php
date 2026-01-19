<?php

namespace App\Helpers;

use App\Models\ConstantDetail;
use App\Models\ConstantMaster;
use Illuminate\Support\Facades\Cache;

class ConstantsHelper
{
    /**
     * الحصول على تفاصيل ثابت معين
     */
    public static function get(int $constantNumber): \Illuminate\Support\Collection
    {
        return Cache::remember("constant_{$constantNumber}", 3600, function () use ($constantNumber) {
            return ConstantDetail::getByConstantNumber($constantNumber);
        });
    }

    /**
     * الحصول على ثابت من اسمه
     */
    public static function getByName(string $constantName): \Illuminate\Support\Collection
    {
        $master = ConstantMaster::findByName($constantName);

        if (! $master) {
            return collect();
        }

        return Cache::remember("constant_name_{$constantName}", 3600, function () use ($master) {
            return $master->details;
        });
    }

    /**
     * الحصول على ثابت واحد من رقمه ورقم التفصيل
     */
    public static function find(int $constantNumber, int $detailId): ?ConstantDetail
    {
        $master = ConstantMaster::findByNumber($constantNumber);

        if (! $master) {
            return null;
        }

        return $master->details()->where('id', $detailId)->first();
    }

    /**
     * الحصول على ثابت واحد من رقمه وترميز التفصيل
     */
    public static function findByCode(int $constantNumber, string $code): ?ConstantDetail
    {
        $master = ConstantMaster::findByNumber($constantNumber);

        if (! $master) {
            return null;
        }

        return $master->details()->where('code', $code)->first();
    }

    /**
     * الحصول على المدن حسب المحافظة
     */
    public static function getCitiesByGovernorate(int $governorateDetailId): \Illuminate\Support\Collection
    {
        return Cache::remember("cities_by_governorate_{$governorateDetailId}", 3600, function () use ($governorateDetailId) {
            $citiesMaster = ConstantMaster::findByNumber(20);
            
            if (!$citiesMaster) {
                return collect();
            }
            
            return ConstantDetail::where('constant_master_id', $citiesMaster->id)
                ->where('parent_detail_id', $governorateDetailId)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        });
    }

    /**
     * الحصول على المدن حسب كود المحافظة
     */
    public static function getCitiesByGovernorateCode(string $governorateCode): \Illuminate\Support\Collection
    {
        $governoratesMaster = ConstantMaster::findByNumber(1);
        
        if (!$governoratesMaster) {
            return collect();
        }
        
        $governorate = ConstantDetail::where('constant_master_id', $governoratesMaster->id)
            ->where('code', $governorateCode)
            ->where('is_active', true)
            ->first();
        
        if (!$governorate) {
            return collect();
        }
        
        return self::getCitiesByGovernorate($governorate->id);
    }

    /**
     * مسح الكاش
     */
    public static function clearCache(?int $constantNumber = null): void
    {
        if ($constantNumber) {
            Cache::forget("constant_{$constantNumber}");
            $master = ConstantMaster::findByNumber($constantNumber);
            if ($master) {
                Cache::forget("constant_name_{$master->constant_name}");
            }
        } else {
            // مسح كل الكاش
            $masters = ConstantMaster::all();
            foreach ($masters as $master) {
                Cache::forget("constant_{$master->constant_number}");
                Cache::forget("constant_name_{$master->constant_name}");
            }
        }
    }
}






