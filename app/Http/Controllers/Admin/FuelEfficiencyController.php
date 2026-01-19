<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFuelEfficiencyRequest;
use App\Http\Requests\Admin\UpdateFuelEfficiencyRequest;
use App\Models\FuelEfficiency;
use App\Models\Generator;
use App\Models\Notification;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelEfficiencyController extends Controller
{
    /**
     * Display paginated list of fuel efficiency records filtered by user role.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $this->authorize('viewAny', FuelEfficiency::class);

        $user = auth()->user();
        
        // التحقق من وجود المشغل ووحدة التوليد على الأقل (اختياري للبحث)
        $hasFilters = $request->filled('operator_id') || $request->filled('generation_unit_id') || $request->filled('generator_id') || $request->filled('date_from') || $request->filled('date_to');
        
        if (!$hasFilters && !$request->ajax() && !$request->wantsJson()) {
            // إذا لم يكن هناك فلاتر وليس طلب AJAX، نرجع الصفحة بدون بيانات أو نحدد الفلاتر تلقائياً
            $operators = collect();
            $generators = collect();
            $generationUnits = collect();
            // إنشاء paginated collection فارغ بدلاً من null
            $fuelEfficiencies = FuelEfficiency::query()->paginate(100);
            $groupedLogs = null;
            $autoSelectGenerationUnit = false;
            
            // SuperAdmin, Admin, EnergyAuthority: يمكنهم رؤية جميع المشغلين
            if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
                $operators = \App\Models\Operator::orderBy('name')->get();
                $generationUnits = \App\Models\GenerationUnit::orderBy('unit_code')->get();
                $generators = \App\Models\Generator::orderBy('generator_number')->get();
            } elseif ($user->isCompanyOwner()) {
                $operator = $user->ownedOperators()->first();
                if ($operator) {
                    $operators = collect([$operator]);
                    $generationUnits = $operator->generationUnits()->orderBy('unit_code')->get();
                    $generators = \App\Models\Generator::whereHas('generationUnit', function($q) use ($operator) {
                        $q->where('operator_id', $operator->id);
                    })->orderBy('generator_number')->get();
                    
                    // إذا كان هناك وحدة توليد واحدة فقط، نحددها تلقائياً
                    if ($generationUnits->count() == 1) {
                        $autoSelectGenerationUnit = $generationUnits->first()->id;
                    }
                }
            } elseif ($user->isEmployee() || $user->isTechnician()) {
                $operators = $user->operators;
                $generationUnits = \App\Models\GenerationUnit::whereHas('operator', function($q) use ($operators) {
                    $q->whereIn('operators.id', $operators->pluck('id'));
                })->orderBy('unit_code')->get();
                $generators = \App\Models\Generator::whereHas('generationUnit', function($q) use ($operators) {
                    $q->whereHas('operator', function($qq) use ($operators) {
                        $qq->whereIn('operators.id', $operators->pluck('id'));
                    });
                })->orderBy('generator_number')->get();
                
                // إذا كان المستخدم لديه مشغل واحد فقط ووحدة توليد واحدة، نحددها تلقائياً
                if ($operators->count() == 1 && $generationUnits->count() == 1) {
                    $autoSelectGenerationUnit = $generationUnits->first()->id;
                }
            }
            
            // إذا تم تحديد وحدة توليد تلقائياً، نعيد توجيه مع الفلاتر المطلوبة
            if ($autoSelectGenerationUnit) {
                $operatorId = $user->isCompanyOwner() 
                    ? $user->ownedOperators()->first()->id 
                    : ($user->isEmployee() || $user->isTechnician() ? $operators->first()->id : null);
                
                if ($operatorId) {
                    return redirect()->route('admin.fuel-efficiencies.index', [
                        'operator_id' => $operatorId,
                        'generation_unit_id' => $autoSelectGenerationUnit
                    ]);
                }
            }
            
            return view('admin.fuel-efficiencies.index', compact('operators', 'generators', 'generationUnits', 'fuelEfficiencies', 'groupedLogs'));
        }
        
        // تضمين المولدات المحذوفة soft delete في السجلات التاريخية
        $query = FuelEfficiency::with([
            'generator.operator', // العلاقة generator تستخدم withTrashed() بشكل افتراضي
            'fuelEfficiencyComparisonDetail',
            'energyEfficiencyComparisonDetail'
        ]);

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->whereHas('generator', function ($q) use ($operator) {
                    $q->where('operator_id', $operator->id);
                });
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $query->whereHas('generator', function ($q) use ($operators) {
                $q->whereIn('operator_id', $operators->pluck('id'));
            });
        }


        // SuperAdmin, Admin, EnergyAuthority: يمكنهم فلترة حسب المشغل المختار
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->whereHas('generator', function ($q) use ($operatorId) {
                    $q->where('operator_id', $operatorId);
                });
            }
        }

        // Filter by generation unit
        $generationUnitId = (int) $request->input('generation_unit_id', 0);
        if ($generationUnitId > 0) {
            $query->whereHas('generator', function ($q) use ($generationUnitId) {
                $q->where('generation_unit_id', $generationUnitId);
            });
        }

        // Filter by generator
        $generatorId = (int) $request->input('generator_id', 0);
        if ($generatorId > 0) {
            $query->where('generator_id', $generatorId);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('consumption_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('consumption_date', '<=', $request->input('date_to'));
        }

        // Paginate - 100 items per page
        $fuelEfficiencies = $query->latest('consumption_date')->paginate(100);

        if ($request->ajax() || $request->wantsJson()) {
            // Return tbody rows only
            $html = view('admin.fuel-efficiencies.partials.tbody-rows', compact('fuelEfficiencies'))->render();
            $pagination = view('admin.fuel-efficiencies.partials.pagination', compact('fuelEfficiencies'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $fuelEfficiencies->total(),
            ]);
        }

        // For normal page load, check if group_by_generator is requested
        $groupByGenerator = $request->boolean('group_by_generator', false);
        $groupedLogs = null;
        if ($groupByGenerator) {
            $groupedLogs = $fuelEfficiencies->groupBy('generator_id');
        }

        $operators = collect();
        $generators = collect();
        $generationUnits = collect();
        
        // SuperAdmin, Admin, EnergyAuthority: يمكنهم اختيار أي مشغل
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operators = \App\Models\Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
            
            // Get generation units based on selected operator or all
            $selectedOperatorId = (int) $request->input('operator_id', 0);
            if ($selectedOperatorId > 0) {
                $generationUnits = \App\Models\GenerationUnit::where('operator_id', $selectedOperatorId)
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
                
                // Get generators based on selected generation unit or operator
                $selectedGenerationUnitId = (int) $request->input('generation_unit_id', 0);
                if ($selectedGenerationUnitId > 0) {
                    $generators = Generator::where('generation_unit_id', $selectedGenerationUnitId)
                        ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                        ->orderBy('generator_number')
                        ->get();
                } else {
                    $generators = Generator::where('operator_id', $selectedOperatorId)
                        ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                        ->orderBy('generator_number')
                        ->get();
                }
            } else {
                $generationUnits = \App\Models\GenerationUnit::select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
                
                $generators = Generator::select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                    ->orderBy('generator_number')
                    ->get();
            }
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]); // إضافة المشغل للعرض
                $generationUnits = $operator->generationUnits()
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
                
                $generators = $operator->generators()->select('generators.id', 'generators.name', 'generators.generator_number', 'generators.operator_id', 'generators.generation_unit_id')
                    ->orderBy('generators.generator_number')
                    ->get();
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $userOperators = $user->operators;
            $operators = $userOperators; // إضافة المشغلين للعرض
            $generationUnits = \App\Models\GenerationUnit::whereHas('operator', function($q) use ($userOperators) {
                $q->whereIn('operators.id', $userOperators->pluck('id'));
            })->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('unit_code')
                ->get();
            
            $generators = Generator::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                ->orderBy('generator_number')
                ->get();
        }

        return view('admin.fuel-efficiencies.index', compact('fuelEfficiencies', 'operators', 'generators', 'generationUnits', 'groupedLogs'));
    }

    /**
     * Show form for creating a new fuel efficiency record entry.
     */
    public function create(): View
    {
        $this->authorize('create', FuelEfficiency::class);

        $user = auth()->user();
        $operators = collect();
        $generators = collect();
        $generationUnits = collect();

        if ($user->isSuperAdmin()) {
            $operators = \App\Models\Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
                $generationUnits = $operator->generationUnits()
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
                
                $generators = $operator->generators()
                    ->select('generators.id', 'generators.name', 'generators.generator_number', 'generators.operator_id', 'generators.generation_unit_id')
                    ->orderBy('generators.generator_number')
                    ->get();
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $userOperators = $user->operators;
            $operators = $userOperators;
            $generationUnits = \App\Models\GenerationUnit::whereHas('operator', function($q) use ($userOperators) {
                $q->whereIn('operators.id', $userOperators->pluck('id'));
            })->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('unit_code')
                ->get();
            
            $generators = Generator::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                ->orderBy('generator_number')
                ->get();
        }

        // جلب الثوابت
        $constants = [
            'fuel_efficiency_comparison' => \App\Helpers\ConstantsHelper::get(17), // مقارنة كفاءة الوقود
            'energy_efficiency_comparison' => \App\Helpers\ConstantsHelper::get(18), // مقارنة كفاءة الطاقة
        ];

        return view('admin.fuel-efficiencies.create', compact('operators', 'generators', 'generationUnits', 'constants'));
    }

    /**
     * Store newly created fuel efficiency record in database.
     */
    public function store(StoreFuelEfficiencyRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', FuelEfficiency::class);

        $data = $request->validated();
        
        // الحصول على operator_id و generation_unit_id من generator إذا لم يتم إرسالهما
        if (!isset($data['operator_id']) || !isset($data['generation_unit_id'])) {
            $generator = Generator::with('generationUnit')->find($data['generator_id']);
            if ($generator) {
                if (!isset($data['operator_id'])) {
                    $data['operator_id'] = $generator->operator_id;
                }
                if (!isset($data['generation_unit_id'])) {
                    $data['generation_unit_id'] = $generator->generation_unit_id;
                }
            }
        }
        
        // Always calculate total operating cost from fuel consumed and price (ignore user input for security)
        if (isset($data['fuel_consumed']) && isset($data['fuel_price_per_liter']) 
            && $data['fuel_consumed'] > 0 && $data['fuel_price_per_liter'] > 0) {
            $data['total_operating_cost'] = round($data['fuel_consumed'] * $data['fuel_price_per_liter'], 2);
        } else {
            $data['total_operating_cost'] = null;
        }
        
        // لا نحسب energy_efficiency_comparison تلقائياً، المستخدم يختار من الثوابت

        $fuelEfficiency = FuelEfficiency::create($data);

        $generator = Generator::with('operator')->find($fuelEfficiency->generator_id);
        if ($generator && $generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'fuel_efficiency_added',
                'تم إضافة سجل كفاءة وقود',
                "تم إضافة سجل كفاءة وقود للمولد: {$generator->name}",
                route('admin.fuel-efficiencies.show', $fuelEfficiency)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء سجل كفاءة الوقود بنجاح.',
            ]);
        }

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم إنشاء سجل كفاءة الوقود بنجاح.');
    }

    /**
     * Display detailed information about the specified fuel efficiency record.
     */
    public function show(FuelEfficiency $fuelEfficiency): View
    {
        $this->authorize('view', $fuelEfficiency);

        $fuelEfficiency->load([
            'generator.operator',
            'fuelEfficiencyComparisonDetail',
            'energyEfficiencyComparisonDetail'
        ]);

        return view('admin.fuel-efficiencies.show', compact('fuelEfficiency'));
    }

    /**
     * Show form for editing the specified fuel efficiency record.
     */
    public function edit(FuelEfficiency $fuelEfficiency): View
    {
        $this->authorize('update', $fuelEfficiency);

        $user = auth()->user();
        $generators = collect();

        if ($user->isSuperAdmin()) {
            $generators = Generator::all();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $generators = $operator->generators;
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
            $generators = Generator::whereIn('operator_id', $operators->pluck('id'))->get();
        }
        
        // جلب الثوابت
        $constants = [
            'fuel_efficiency_comparison' => \App\Helpers\ConstantsHelper::get(17), // مقارنة كفاءة الوقود
            'energy_efficiency_comparison' => \App\Helpers\ConstantsHelper::get(18), // مقارنة كفاءة الطاقة
        ];

        return view('admin.fuel-efficiencies.edit', compact('fuelEfficiency', 'generators', 'constants'));
    }

    /**
     * Update the specified fuel efficiency record data in database.
     */
    public function update(UpdateFuelEfficiencyRequest $request, FuelEfficiency $fuelEfficiency): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $fuelEfficiency);

        $data = $request->validated();
        
        // Always calculate total operating cost from fuel consumed and price (ignore user input for security)
        if (isset($data['fuel_consumed']) && isset($data['fuel_price_per_liter']) 
            && $data['fuel_consumed'] > 0 && $data['fuel_price_per_liter'] > 0) {
            $data['total_operating_cost'] = round($data['fuel_consumed'] * $data['fuel_price_per_liter'], 2);
        } else {
            $data['total_operating_cost'] = null;
        }
        
        // لا نحسب energy_efficiency_comparison تلقائياً، المستخدم يختار من الثوابت

        $fuelEfficiency->update($data);

        $fuelEfficiency->load('generator.operator');
        if ($fuelEfficiency->generator && $fuelEfficiency->generator->operator) {
            Notification::notifyOperatorUsers(
                $fuelEfficiency->generator->operator,
                'fuel_efficiency_updated',
                'تم تحديث سجل كفاءة وقود',
                "تم تحديث سجل كفاءة وقود للمولد: {$fuelEfficiency->generator->name}",
                route('admin.fuel-efficiencies.show', $fuelEfficiency)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سجل كفاءة الوقود بنجاح.',
            ]);
        }

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم تحديث سجل كفاءة الوقود بنجاح.');
    }

    /**
     * Remove the specified fuel efficiency record from database.
     */
    public function destroy(Request $request, FuelEfficiency $fuelEfficiency): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $fuelEfficiency);

        $fuelEfficiency->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف سجل كفاءة الوقود بنجاح.',
            ]);
        }

        return redirect()->route('admin.fuel-efficiencies.index')
            ->with('success', 'تم حذف سجل كفاءة الوقود بنجاح.');
    }

    /**
     * Get generation units for a specific operator (AJAX).
     */
    public function getGenerationUnits(Request $request, Operator $operator): JsonResponse
    {
        $this->authorize('view', $operator);

        $generationUnits = $operator->generationUnits()
            ->select('id', 'name', 'unit_code', 'unit_number')
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'unit_code' => $unit->unit_code,
                    'unit_number' => $unit->unit_number,
                    'label' => "{$unit->name} ({$unit->unit_code})",
                ];
            });

        return response()->json([
            'success' => true,
            'generation_units' => $generationUnits,
        ]);
    }

    /**
     * Get generators for a specific generation unit (AJAX).
     */
    public function getGenerators(Request $request, \App\Models\GenerationUnit $generationUnit): JsonResponse
    {
        $this->authorize('view', $generationUnit);

        $generators = $generationUnit->generators()
            ->select('id', 'name', 'generator_number', 'generation_unit_id')
            ->get()
            ->map(function ($generator) {
                return [
                    'id' => $generator->id,
                    'name' => $generator->name,
                    'generator_number' => $generator->generator_number,
                    'label' => "{$generator->generator_number} - {$generator->name}",
                ];
            });

        return response()->json([
            'success' => true,
            'generators' => $generators,
        ]);
    }
}
