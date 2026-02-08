<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GenerationUnit;
use App\Models\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicQrController extends Controller
{
    /**
     * عرض معلومات وحدة التوليد من QR Code
     */
    public function generationUnit(string $code): View|RedirectResponse
    {
        $generationUnit = GenerationUnit::where('unit_code', $code)
            ->with(['operator', 'statusDetail', 'city'])
            ->first();

        if (!$generationUnit) {
            return redirect()->route('front.home')
                ->with('error', 'وحدة التوليد غير موجودة.');
        }

        return view('public.qr.generation-unit', compact('generationUnit'));
    }

    /**
     * عرض معلومات المولد من QR Code
     * - المتصفح: HTML
     * - التطبيق (Accept: application/json أو ?format=json): JSON
     */
    public function generator(Request $request, string $code): View|RedirectResponse|JsonResponse
    {
        $generator = Generator::where('generator_number', $code)
            ->with(['operator', 'generationUnit', 'statusDetail'])
            ->first();

        if (!$generator) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'المولد غير موجود.',
                ], 404);
            }
            return redirect()->route('front.home')
                ->with('error', 'المولد غير موجود.');
        }

        // التطبيق المحمول يطلب JSON
        if ($this->wantsJson($request)) {
            return response()->json([
                'success' => true,
                'message' => 'تم جلب بيانات المولد بنجاح.',
                'data' => [
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

        return view('public.qr.generator', compact('generator'));
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson()
            || $request->wantsJson()
            || $request->query('format') === 'json';
    }
}



