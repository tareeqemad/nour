<?php

namespace App\Http\Controllers\Admin;

use App\Governorate;
use App\Helpers\ConstantsHelper;
use App\Http\Controllers\Controller;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperatorUnitNumberController extends Controller
{
    /**
     * الحصول على رقم الوحدة التالي للمحافظة
     */
    public function getNextUnitNumber(int $governorate): JsonResponse
    {
        $governorateEnum = Governorate::fromValue($governorate);

        if (! $governorateEnum) {
            return response()->json([
                'success' => false,
                'message' => 'المحافظة غير صحيحة',
            ], 400);
        }

        $unitNumber = Operator::getNextUnitNumber($governorateEnum);

        return response()->json([
            'success' => true,
            'unit_number' => $unitNumber,
            'governorate_code' => $governorateEnum->code(),
        ]);
    }

    /**
     * توليد رقم الوحدة وكود الوحدة بناءً على المحافظة والمدينة
     */
    public function generateUnitCode(Request $request): JsonResponse
    {
        $request->validate([
            'governorate_code' => 'required|string',
            'city_id' => 'required|integer|exists:constant_details,id',
        ]);

        // الحصول على المحافظة من الثوابت
        $governorateDetail = ConstantsHelper::findByCode(1, $request->governorate_code);
        if (!$governorateDetail || !$governorateDetail->value) {
            return response()->json([
                'success' => false,
                'message' => 'المحافظة غير صحيحة',
            ], 400);
        }

        $governorateEnum = Governorate::fromValue((int) $governorateDetail->value);
        if (!$governorateEnum) {
            return response()->json([
                'success' => false,
                'message' => 'المحافظة غير صحيحة',
            ], 400);
        }

        // توليد رقم الوحدة
        $unitNumber = Operator::getNextUnitNumber($governorateEnum, $request->city_id);
        
        // توليد كود الوحدة
        $unitCode = Operator::generateUnitCode($governorateEnum, $request->city_id, $unitNumber);

        return response()->json([
            'success' => true,
            'unit_number' => $unitNumber,
            'unit_code' => $unitCode,
        ]);
    }
}
