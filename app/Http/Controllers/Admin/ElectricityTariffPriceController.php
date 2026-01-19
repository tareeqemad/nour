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
     * Display a listing of electricity tariff prices for an operator.
     */
    public function index(Operator $operator): View
    {
        $this->authorize('view', $operator);
        $this->authorize('viewAny', ElectricityTariffPrice::class);

        $tariffPrices = $operator->electricityTariffPrices()
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.electricity-tariff-prices.index', compact('operator', 'tariffPrices'));
    }

    /**
     * Show the form for creating a new electricity tariff price.
     */
    public function create(Operator $operator): View
    {
        $this->authorize('update', $operator);
        $this->authorize('create', ElectricityTariffPrice::class);

        return view('admin.electricity-tariff-prices.create', compact('operator'));
    }

    /**
     * Store a newly created electricity tariff price.
     */
    public function store(Request $request, Operator $operator): RedirectResponse
    {
        $this->authorize('update', $operator);
        $this->authorize('create', ElectricityTariffPrice::class);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'price_per_kwh' => ['required', 'numeric', 'min:0', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['operator_id'] = $operator->id;
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Deactivate overlapping prices
        if ($validated['is_active']) {
            ElectricityTariffPrice::where('operator_id', $operator->id)
                ->where('is_active', true)
                ->where(function ($query) use ($validated) {
                    $query->where(function ($q) use ($validated) {
                        // Overlapping: existing start_date is within new range
                        $q->where('start_date', '>=', $validated['start_date'])
                          ->where(function ($sq) use ($validated) {
                              $sq->whereNull('end_date')
                                 ->orWhere('end_date', '>=', $validated['start_date']);
                          });
                    })
                    ->orWhere(function ($q) use ($validated) {
                        // Overlapping: existing end_date is within new range
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

        return redirect()->route('admin.operators.tariff-prices.index', $operator)
            ->with('success', 'تم إضافة سعر التعرفة بنجاح.');
    }

    /**
     * Show the form for editing the specified electricity tariff price.
     */
    public function edit(Operator $operator, ElectricityTariffPrice $tariffPrice): View
    {
        $this->authorize('update', $operator);
        $this->authorize('update', $tariffPrice);

        return view('admin.electricity-tariff-prices.edit', compact('operator', 'tariffPrice'));
    }

    /**
     * Update the specified electricity tariff price.
     */
    public function update(Request $request, Operator $operator, ElectricityTariffPrice $tariffPrice): RedirectResponse
    {
        $this->authorize('update', $operator);
        $this->authorize('update', $tariffPrice);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'price_per_kwh' => ['required', 'numeric', 'min:0', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        // Deactivate overlapping prices (excluding current one)
        if ($validated['is_active']) {
            ElectricityTariffPrice::where('operator_id', $operator->id)
                ->where('id', '!=', $tariffPrice->id)
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

        $tariffPrice->update($validated);

        return redirect()->route('admin.operators.tariff-prices.index', $operator)
            ->with('success', 'تم تحديث سعر التعرفة بنجاح.');
    }

    /**
     * Remove the specified electricity tariff price.
     */
    public function destroy(Operator $operator, ElectricityTariffPrice $tariffPrice): RedirectResponse
    {
        $this->authorize('update', $operator);
        $this->authorize('delete', $tariffPrice);

        $tariffPrice->delete();

        return redirect()->route('admin.operators.tariff-prices.index', $operator)
            ->with('success', 'تم حذف سعر التعرفة بنجاح.');
    }

    /**
     * API: Get tariff price for operator on specific date
     */
    public function getTariffPrice(Operator $operator, Request $request): JsonResponse
    {
        $this->authorize('view', $operator);
        $this->authorize('viewAny', ElectricityTariffPrice::class);

        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $tariffPrice = ElectricityTariffPrice::getActivePriceForDate($operator->id, Carbon::parse($date));

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
