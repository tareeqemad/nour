<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GenerationUnit;
use App\Models\Generator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
     */
    public function generator(string $code): View|RedirectResponse
    {
        $generator = Generator::where('generator_number', $code)
            ->with(['operator', 'generationUnit', 'statusDetail'])
            ->first();

        if (!$generator) {
            return redirect()->route('front.home')
                ->with('error', 'المولد غير موجود.');
        }

        return view('public.qr.generator', compact('generator'));
    }
}



