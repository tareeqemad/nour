<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectricityTariffPrice;
use App\Models\Operator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ElectricityTariffPriceController extends Controller
{
    /**
     * Display a listing of electricity tariff prices (general prices for all operators).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ElectricityTariffPrice::class);

        // الحصول على الأسعار العامة
        $query = ElectricityTariffPrice::with('creator') // تحميل من أضاف السعر
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc');

        $tariffPrices = $query->paginate(20);
        $operator = null; // للتوافق مع الـ view

        return view('admin.electricity-tariff-prices.index', compact('tariffPrices', 'operator'));
    }

    /**
     * Show the form for creating a new electricity tariff price.
     */
    public function create(): View
    {
        $this->authorize('create', ElectricityTariffPrice::class);

        return view('admin.electricity-tariff-prices.create');
    }

    /**
     * Store a newly created electricity tariff price.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ElectricityTariffPrice::class);

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'max:' . date('Y-m-d')],
            'end_date' => ['nullable', 'date', 'after:start_date', 'max:' . date('Y-m-d')],
            'price_per_kwh' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // الأسعار عامة لجميع المشغلين
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['created_by'] = auth()->id(); // تتبع من أضاف السعر

        // Deactivate overlapping prices
        if ($validated['is_active']) {
            ElectricityTariffPrice::where('is_active', true)
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('start_date', '>=', $validated['start_date'])
                          ->where(function ($sq) use ($validated) {
                              $sq->whereNull('end_date')
                                 ->orWhere('end_date', '>=', $validated['start_date']);
                          });
                    })
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                          ->where(function ($sq) use ($validated) {
                              $sq->whereNull('end_date')
                                 ->orWhere(function ($ssq) use ($validated) {
                                     $ssq->whereNotNull('end_date')
                                        ->where('end_date', '>=', $validated['start_date']);
                                 });
                          });
                    });
                })
                ->update(['is_active' => false]);
        }

        ElectricityTariffPrice::create($validated);

        return redirect()->route('admin.electricity-tariff-prices.index')
            ->with('success', 'تم إضافة سعر التعرفة بنجاح.');
    }

    /**
     * Show the form for editing the specified electricity tariff price.
     */
    public function edit(ElectricityTariffPrice $electricityTariffPrice): View
    {
        $this->authorize('update', $electricityTariffPrice);

        $tariffPrice = $electricityTariffPrice;
        return view('admin.electricity-tariff-prices.edit', compact('tariffPrice'));
    }

    /**
     * Update the specified electricity tariff price.
     */
    public function update(Request $request, ElectricityTariffPrice $electricityTariffPrice): RedirectResponse
    {
        $this->authorize('update', $electricityTariffPrice);

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'max:' . date('Y-m-d')],
            'end_date' => ['nullable', 'date', 'after:start_date', 'max:' . date('Y-m-d')],
            'price_per_kwh' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        // Deactivate overlapping prices (excluding current one)
        if ($validated['is_active']) {
            ElectricityTariffPrice::where('id', '!=', $electricityTariffPrice->id)
                ->where('is_active', true)
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('start_date', '>=', $validated['start_date'])
                          ->where(function ($sq) use ($validated) {
                              $sq->whereNull('end_date')
                                 ->orWhere('end_date', '>=', $validated['start_date']);
                          });
                    })
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('start_date', '<=', $validated['start_date'])
                          ->where(function ($sq) use ($validated) {
                              $sq->whereNull('end_date')
                                 ->orWhere(function ($ssq) use ($validated) {
                                     $ssq->whereNotNull('end_date')
                                        ->where('end_date', '>=', $validated['start_date']);
                                 });
                          });
                    });
                })
                ->update(['is_active' => false]);
        }

        $electricityTariffPrice->update($validated);

        return redirect()->route('admin.electricity-tariff-prices.index')
            ->with('success', 'تم تحديث سعر التعرفة بنجاح.');
    }

    /**
     * Remove the specified electricity tariff price.
     */
    public function destroy(ElectricityTariffPrice $electricityTariffPrice): RedirectResponse
    {
        $this->authorize('delete', $electricityTariffPrice);

        $electricityTariffPrice->delete();

        return redirect()->route('admin.electricity-tariff-prices.index')
            ->with('success', 'تم حذف سعر التعرفة بنجاح.');
    }

    /**
     * Display a listing of electricity tariff prices for an operator (legacy route - للعرض فقط).
     * يعرض الأسعار العامة فقط لأن الأسعار الآن عامة لجميع المشغلين.
     */
    public function indexForOperator(Operator $operator): View
    {
        $this->authorize('view', $operator);
        $this->authorize('viewAny', ElectricityTariffPrice::class);

        // عرض الأسعار العامة
        $tariffPrices = ElectricityTariffPrice::with('creator') // تحميل من أضاف السعر
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.electricity-tariff-prices.index', compact('operator', 'tariffPrices'));
    }

    /**
     * API: Get tariff price for specific date (الأسعار عامة لجميع المشغلين)
     */
    public function getTariffPrice(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ElectricityTariffPrice::class);

        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $tariffPrice = ElectricityTariffPrice::getActivePriceForDate(Carbon::parse($date));

        if ($tariffPrice) {
            return response()->json([
                'price' => $tariffPrice->price_per_kwh,
                'start_date' => $tariffPrice->start_date->format('Y-m-d'),
                'end_date' => $tariffPrice->end_date?->format('Y-m-d'),
            ]);
        }

        return response()->json(['price' => null]);
    }
}
