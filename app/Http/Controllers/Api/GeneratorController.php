<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Generator;
use Illuminate\Http\JsonResponse;

class GeneratorController extends Controller
{
    /**
     * الحصول على بيانات المولد من رقم QR (للتطبيق المحمول)
     * 
     * API عام - يمكن استدعاؤه بدون تسجيل دخول (عند مسح QR Code)
     * 
     * @param string $code رقم المولد (generator_number) - مثل GU-GZ-GZ-001-G01
     */
    public function qrData(string $code): JsonResponse
    {
        $generator = Generator::where('generator_number', $code)
            ->with(['operator', 'generationUnit', 'statusDetail'])
            ->first();

        if (!$generator) {
            return response()->json([
                'success' => false,
                'message' => 'المولد غير موجود.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب بيانات المولد بنجاح.',
            'data' => [
                // بيانات العرض (كما في صفحة QR Code)
                'display' => [
                    'generator_number' => $generator->generator_number ?? 'GEN-' . $generator->id,
                    'name' => $generator->name,
                    'operator_name' => $generator->operator?->name,
                    'generation_unit_name' => $generator->generationUnit
                        ? $generator->generationUnit->name . ' (' . $generator->generationUnit->unit_code . ')'
                        : null,
                    'capacity' => $generator->capacity_kva
                        ? number_format($generator->capacity_kva, 0) . ' KVA'
                        : null,
                    'status' => $generator->statusDetail?->label ?? 'غير محدد',
                    'note' => 'QR Code يحتوي على رقم المولد فقط. استخدمه للبحث في النظام.',
                ],
                // بيانات التخزين للمطور (IDs وبيانات خام)
                'generator' => [
                    'id' => $generator->id,
                    'name' => $generator->name,
                    'generator_number' => $generator->generator_number ?? 'GEN-' . $generator->id,
                    'capacity_kva' => $generator->capacity_kva,
                    'operator_id' => $generator->operator_id,
                    'generation_unit_id' => $generator->generation_unit_id,
                    'status_id' => $generator->status_id,
                ],
                'operator' => $generator->operator ? [
                    'id' => $generator->operator->id,
                    'name' => $generator->operator->name,
                ] : null,
                'generation_unit' => $generator->generationUnit ? [
                    'id' => $generator->generationUnit->id,
                    'name' => $generator->generationUnit->name,
                    'unit_code' => $generator->generationUnit->unit_code,
                ] : null,
            ],
        ]);
    }
}
