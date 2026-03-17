<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDiscountRate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDiscountRateController extends Controller
{
    /**
     * Display a listing of employee discount rates.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', EmployeeDiscountRate::class);

        $discountRates = EmployeeDiscountRate::with('creator')
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.employee-discount-rates.index', compact('discountRates'));
    }

    /**
     * Show the form for creating a new employee discount rate.
     */
    public function create(): View
    {
        $this->authorize('create', EmployeeDiscountRate::class);

        return view('admin.employee-discount-rates.create');
    }

    /**
     * Store a newly created employee discount rate.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmployeeDiscountRate::class);

        $validated = $request->validate([
            'start_date'    => ['required', 'date', 'before_or_equal:today'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:today'],
            'discount_rate' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'is_active'     => ['nullable', 'boolean'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active']   = $validated['is_active'] ?? true;
        $validated['created_by']  = auth()->id();

        // تعطيل النسب المتداخلة عند الإضافة بحالة نشطة
        if ($validated['is_active']) {
            EmployeeDiscountRate::where('is_active', true)
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

        EmployeeDiscountRate::create($validated);

        return redirect()->route('admin.employee-discount-rates.index')
            ->with('success', 'تم إضافة نسبة خصم الموظفين بنجاح.');
    }

    /**
     * Show the form for editing the specified employee discount rate.
     */
    public function edit(EmployeeDiscountRate $employeeDiscountRate): View
    {
        $this->authorize('update', $employeeDiscountRate);

        $discountRate = $employeeDiscountRate;
        return view('admin.employee-discount-rates.edit', compact('discountRate'));
    }

    /**
     * Update the specified employee discount rate.
     */
    public function update(Request $request, EmployeeDiscountRate $employeeDiscountRate): RedirectResponse
    {
        $this->authorize('update', $employeeDiscountRate);

        $validated = $request->validate([
            'start_date'    => ['required', 'date', 'before_or_equal:today'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:today'],
            'discount_rate' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'],
            'is_active'     => ['nullable', 'boolean'],
            'notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        // تعطيل النسب المتداخلة (باستثناء السجل الحالي)
        if ($validated['is_active']) {
            EmployeeDiscountRate::where('id', '!=', $employeeDiscountRate->id)
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

        $employeeDiscountRate->update($validated);

        return redirect()->route('admin.employee-discount-rates.index')
            ->with('success', 'تم تحديث نسبة خصم الموظفين بنجاح.');
    }

    /**
     * Remove the specified employee discount rate.
     */
    public function destroy(EmployeeDiscountRate $employeeDiscountRate): RedirectResponse
    {
        $this->authorize('delete', $employeeDiscountRate);

        $employeeDiscountRate->delete();

        return redirect()->route('admin.employee-discount-rates.index')
            ->with('success', 'تم حذف نسبة خصم الموظفين بنجاح.');
    }

    /**
     * API: Get discount rate for specific date
     */
    public function getDiscountRate(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EmployeeDiscountRate::class);

        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $discountRate = EmployeeDiscountRate::getActiveRateForDate(Carbon::parse($date));

        if ($discountRate) {
            return response()->json([
                'discount_rate' => $discountRate->discount_rate,
                'start_date'    => $discountRate->start_date->format('Y-m-d'),
                'end_date'      => $discountRate->end_date?->format('Y-m-d'),
            ]);
        }

        return response()->json(['discount_rate' => null]);
    }
}
