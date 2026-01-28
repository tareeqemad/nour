<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOperationLogRequest;
use App\Http\Requests\Admin\UpdateOperationLogRequest;
use App\Models\Generator;
use App\Models\Notification;
use App\Models\OperationLog;
use App\Models\Operator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationLogController extends Controller
{
    /**
     * Display paginated operation logs with search and filters.
     */
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        $this->authorize('viewAny', OperationLog::class);

        $user = auth()->user();
        
        // التحقق من وجود المشغل ووحدة التوليد على الأقل
        $hasRequiredFilters = $request->filled('operator_id') && $request->filled('generation_unit_id');
        
        if (!$hasRequiredFilters && !$request->ajax() && !$request->wantsJson()) {
            // إذا لم يكن هناك فلاتر وليس طلب AJAX، نرجع الصفحة بدون بيانات
            $operators = collect();
            $generators = collect();
            $generationUnits = collect();
            $operationLogs = null;
            $groupedLogs = null;
            $autoSelectGenerationUnit = false;
            
            // SuperAdmin, Admin, EnergyAuthority: يمكنهم رؤية جميع المشغلين
            if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
                $operators = \App\Models\Operator::orderBy('name')->get();
                $generationUnits = \App\Models\GenerationUnit::orderBy('name')->get();
                $generators = \App\Models\Generator::orderBy('name')->get();
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
                    return redirect()->route('admin.operation-logs.index', [
                        'operator_id' => $operatorId,
                        'generation_unit_id' => $autoSelectGenerationUnit
                    ]);
                }
            }
            
            return view('admin.operation-logs.index', compact('operators', 'generators', 'generationUnits', 'operationLogs', 'groupedLogs'));
        }
        
        // تضمين المولدات المحذوفة soft delete في السجلات التاريخية
        $query = OperationLog::with(['generator', 'operator']); // العلاقة generator تستخدم withTrashed() بشكل افتراضي

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $query->whereIn('operator_id', $operators->pluck('id'));
        }


        // SuperAdmin, Admin, EnergyAuthority: يمكنهم فلترة حسب المشغل المختار
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operatorId = (int) $request->input('operator_id', 0);
            if ($operatorId > 0) {
                $query->where('operator_id', $operatorId);
            }
        }

        // Filter by generator
        $generatorId = (int) $request->input('generator_id', 0);
        if ($generatorId > 0) {
            $query->where('generator_id', $generatorId);
        }

        // Filter by generation unit
        $generationUnitId = (int) $request->input('generation_unit_id', 0);
        if ($generationUnitId > 0) {
            $query->whereHas('generator', function($q) use ($generationUnitId) {
                $q->where('generation_unit_id', $generationUnitId);
            });
        }


        // Filter by load percentage with operators
        if ($request->filled('load_percentage_value') && $request->filled('load_percentage_operator')) {
            $loadValue = (float) $request->input('load_percentage_value');
            $loadOperator = $request->input('load_percentage_operator');
            
            switch ($loadOperator) {
                case 'equals':
                    $query->where('load_percentage', $loadValue);
                    break;
                case 'greater_than':
                    $query->where('load_percentage', '>', $loadValue);
                    break;
                case 'less_than':
                    $query->where('load_percentage', '<', $loadValue);
                    break;
                case 'greater_equal':
                    $query->where('load_percentage', '>=', $loadValue);
                    break;
                case 'less_equal':
                    $query->where('load_percentage', '<=', $loadValue);
                    break;
            }
        }

        // Filter by fuel consumed with operators
        if ($request->filled('fuel_consumed_value') && $request->filled('fuel_consumed_operator')) {
            $fuelValue = (float) $request->input('fuel_consumed_value');
            $fuelOperator = $request->input('fuel_consumed_operator');
            
            switch ($fuelOperator) {
                case 'equals':
                    $query->where('fuel_consumed', $fuelValue);
                    break;
                case 'greater_than':
                    $query->where('fuel_consumed', '>', $fuelValue);
                    break;
                case 'less_than':
                    $query->where('fuel_consumed', '<', $fuelValue);
                    break;
                case 'greater_equal':
                    $query->where('fuel_consumed', '>=', $fuelValue);
                    break;
                case 'less_equal':
                    $query->where('fuel_consumed', '<=', $fuelValue);
                    break;
            }
        }

        // Filter by energy produced with operators
        if ($request->filled('energy_produced_value') && $request->filled('energy_produced_operator')) {
            $energyValue = (float) $request->input('energy_produced_value');
            $energyOperator = $request->input('energy_produced_operator');
            
            switch ($energyOperator) {
                case 'equals':
                    $query->where('energy_produced', $energyValue);
                    break;
                case 'greater_than':
                    $query->where('energy_produced', '>', $energyValue);
                    break;
                case 'less_than':
                    $query->where('energy_produced', '<', $energyValue);
                    break;
                case 'greater_equal':
                    $query->where('energy_produced', '>=', $energyValue);
                    break;
                case 'less_equal':
                    $query->where('energy_produced', '<=', $energyValue);
                    break;
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('operation_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('operation_date', '<=', $request->input('date_to'));
        }

        // حساب الإحصائيات الإجمالية من جميع السجلات المطابقة (قبل pagination)
        $totalStats = [
            'total_count' => $query->count(),
            'total_fuel' => $query->sum('fuel_consumed') ?? 0,
            'total_energy' => $query->sum('energy_produced') ?? 0,
            'total_duration' => 0, // سيتم حسابه لاحقاً
        ];
        
        // حساب المدة الإجمالية
        $allLogsForDuration = (clone $query)->select('operation_date', 'start_time', 'end_time')->get();
        foreach ($allLogsForDuration as $log) {
            if ($log->start_time && $log->end_time) {
                $startTime = \Carbon\Carbon::parse($log->operation_date->format('Y-m-d') . ' ' . $log->start_time->format('H:i:s'));
                $endTime = \Carbon\Carbon::parse($log->operation_date->format('Y-m-d') . ' ' . $log->end_time->format('H:i:s'));
                if ($endTime->lt($startTime)) {
                    $endTime->addDay();
                }
                $totalStats['total_duration'] += $startTime->diffInMinutes($endTime);
            }
        }
        
        // Paginate - 100 items per page
        $operationLogs = $query->latest('operation_date')->latest('start_time')->paginate(100);

        if ($request->ajax() || $request->wantsJson()) {
            // Check if grouping is requested - التجميع حسب المولد فقط
            $groupByGenerator = $request->boolean('group_by_generator', false);
            
            if ($groupByGenerator) {
                $groupedLogs = $operationLogs->groupBy('generator_id');
                $html = view('admin.operation-logs.partials.grouped-list', compact('groupedLogs', 'operationLogs', 'totalStats'))->render();
            } else {
                $html = view('admin.operation-logs.partials.list', compact('operationLogs', 'totalStats'))->render();
            }
            
            $pagination = view('admin.operation-logs.partials.pagination', compact('operationLogs'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $operationLogs->total(),
            ]);
        }

        // For normal page load, check grouping options - التجميع حسب المولد فقط
        $groupByGenerator = $request->boolean('group_by_generator', false);
        $groupedLogs = null;
        
        if ($groupByGenerator) {
            $groupedLogs = $operationLogs->groupBy('generator_id');
        }

        $operators = collect();
        $generators = collect();
        $generationUnits = collect();
        
        // SuperAdmin, Admin, EnergyAuthority: يمكنهم اختيار أي مشغل
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
            
            // Get generators based on selected operator or all
            $selectedOperatorId = (int) $request->input('operator_id', 0);
            if ($selectedOperatorId > 0) {
                $generators = Generator::where('operator_id', $selectedOperatorId)
                    ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                    ->orderBy('generator_number')
                    ->get();
                
                $generationUnits = \App\Models\GenerationUnit::where('operator_id', $selectedOperatorId)
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
            } else {
                $generators = Generator::select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                    ->orderBy('generator_number')
                    ->get();
                
                $generationUnits = \App\Models\GenerationUnit::select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
            }
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]); // إضافة المشغل للعرض
                $generators = $operator->generators()->select('generators.id', 'generators.name', 'generators.generator_number', 'generators.operator_id', 'generators.generation_unit_id')
                    ->orderBy('generators.generator_number')
                    ->get();
                
                $generationUnits = $operator->generationUnits()
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $userOperators = $user->operators;
            $operators = $userOperators; // إضافة المشغلين للعرض
            $generators = Generator::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                ->orderBy('generator_number')
                ->get();
            
            $generationUnits = \App\Models\GenerationUnit::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('unit_code')
                ->get();
        }

        return view('admin.operation-logs.index', compact('operationLogs', 'operators', 'generators', 'generationUnits', 'groupedLogs', 'totalStats'));
    }

    /**
     * Show form for creating a new operation log entry.
     */
    public function create(): View
    {
        $this->authorize('create', OperationLog::class);

        $user = auth()->user();
        $generators = collect();
        $operators = collect();
        $generationUnits = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
            // لا نحمل وحدات التوليد والمولدات إلا بعد اختيار المشغل
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
                // تحميل وحدات التوليد تلقائياً للمشغل
                $generationUnits = $operator->generationUnits;
                // تحميل المولدات تلقائياً
                $generators = $operator->generators;
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->count() === 1) {
                // إذا كان موظف تابع لمشغل واحد، نحمل وحدات التوليد والمولدات تلقائياً
                $operator = $operators->first();
                $generationUnits = $operator->generationUnits;
                $generators = $operator->generators;
            }
        }

        return view('admin.operation-logs.create', compact('generators', 'operators', 'generationUnits'));
    }

    /**
     * Store a newly created operation log in database.
     */
    public function store(StoreOperationLogRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', OperationLog::class);

        $data = $request->validated();

        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        // حساب التسلسل بناءً على المشغل + وحدة التوليد + المولد
        // الحصول على generation_unit_id من المولد
        $generator = Generator::with('operator')->find($data['generator_id']);
        $operator = Operator::find($data['operator_id']);
        $generationUnitId = $generator ? $generator->generation_unit_id : null;
        
        // حساب آخر تسلسل للمجموعة (operator_id + generation_unit_id + generator_id)
        $lastSequence = OperationLog::join('generators', 'operation_logs.generator_id', '=', 'generators.id')
            ->where('operation_logs.operator_id', $data['operator_id'])
            ->where('operation_logs.generator_id', $data['generator_id'])
            ->when($generationUnitId, function($query) use ($generationUnitId) {
                return $query->where('generators.generation_unit_id', $generationUnitId);
            })
            ->max('operation_logs.sequence') ?? 0;
        $data['sequence'] = $lastSequence + 1;
        

        // Always calculate fuel consumed from start and end (ignore user input for security)
        // المعادلة: كمية الوقود المستهلك (لتر) = قراءة عداد الوقود عند الانتهاء - قراءة عداد الوقود عند البدء
        if (isset($data['fuel_meter_start']) && isset($data['fuel_meter_end']) 
            && $data['fuel_meter_start'] > 0 && $data['fuel_meter_end'] > 0 
            && $data['fuel_meter_end'] >= $data['fuel_meter_start']) {
            $data['fuel_consumed'] = round($data['fuel_meter_end'] - $data['fuel_meter_start'], 2);
        } else {
            $data['fuel_consumed'] = null;
        }

        // Always calculate energy produced from start and end (ignore user input for security)
        // المعادلة: كمية الطاقة المنتجة (kWh) = قراءة عداد الطاقة عند الإيقاف - قراءة عداد الطاقة عند البدء
        if (isset($data['energy_meter_start']) && isset($data['energy_meter_end']) 
            && $data['energy_meter_start'] > 0 && $data['energy_meter_end'] > 0 
            && $data['energy_meter_end'] >= $data['energy_meter_start']) {
            $data['energy_produced'] = round($data['energy_meter_end'] - $data['energy_meter_start'], 2);
        } else {
            $data['energy_produced'] = null;
        }

        // Auto-fill electricity tariff price if not provided
        if (!isset($data['electricity_tariff_price']) || empty($data['electricity_tariff_price'])) {
            $operationDate = Carbon::parse($data['operation_date']);
            $tariffPrice = \App\Models\ElectricityTariffPrice::getActivePriceForDate(
                $data['operator_id'],
                $operationDate
            );
            
            if ($tariffPrice) {
                $data['electricity_tariff_price'] = $tariffPrice->price_per_kwh;
            }
        }

        $operationLog = OperationLog::create($data);

        $generator = Generator::with('operator')->find($data['generator_id']);
        if ($generator && $generator->operator) {
            Notification::notifyOperatorUsers(
                $generator->operator,
                'operation_log_added',
                'تم إضافة سجل تشغيل',
                "تم إضافة سجل تشغيل جديد للمولد: {$generator->name}",
                route('admin.operation-logs.show', $operationLog)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء سجل التشغيل بنجاح.',
            ]);
        }

        return redirect()->route('admin.operation-logs.index')
            ->with('success', 'تم إنشاء سجل التشغيل بنجاح.');
    }

    /**
     * Display details of the specified operation log record.
     */
    public function show(OperationLog $operationLog): View
    {
        $this->authorize('view', $operationLog);

        $operationLog->load(['generator', 'operator']);

        return view('admin.operation-logs.show', compact('operationLog'));
    }

    /**
     * Show form for editing the specified operation log entry.
     */
    public function edit(OperationLog $operationLog): View
    {
        $this->authorize('update', $operationLog);

        $user = auth()->user();
        $generators = collect();
        $operators = collect();
        $generationUnits = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
            // لا نحمل وحدات التوليد والمولدات إلا بعد اختيار المشغل
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
                // تحميل وحدات التوليد تلقائياً للمشغل
                $generationUnits = $operator->generationUnits;
                // تحميل المولدات تلقائياً
                $generators = $operator->generators;
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            if ($operators->count() === 1) {
                // إذا كان موظف تابع لمشغل واحد، نحمل وحدات التوليد والمولدات تلقائياً
                $operator = $operators->first();
                $generationUnits = $operator->generationUnits;
                $generators = $operator->generators;
            }
        }

        return view('admin.operation-logs.edit', compact('operationLog', 'generators', 'operators', 'generationUnits'));
    }

    /**
     * Update the specified operation log record in database.
     */
    public function update(UpdateOperationLogRequest $request, OperationLog $operationLog): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $operationLog);

        $data = $request->validated();

        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        // إعادة حساب التسلسل إذا تم تغيير المشغل أو المولد
        $operatorChanged = isset($data['operator_id']) && $operationLog->operator_id != $data['operator_id'];
        $generatorChanged = isset($data['generator_id']) && $operationLog->generator_id != $data['generator_id'];
        
        if ($operatorChanged || $generatorChanged) {
            // الحصول على generation_unit_id من المولد الجديد
            $newGeneratorId = $data['generator_id'] ?? $operationLog->generator_id;
            $newOperatorId = $data['operator_id'] ?? $operationLog->operator_id;
            
            $generator = Generator::find($newGeneratorId);
            $generationUnitId = $generator ? $generator->generation_unit_id : null;
            
            // حساب آخر تسلسل للمجموعة الجديدة
            $lastSequence = OperationLog::join('generators', 'operation_logs.generator_id', '=', 'generators.id')
                ->where('operation_logs.operator_id', $newOperatorId)
                ->where('operation_logs.generator_id', $newGeneratorId)
                ->where('operation_logs.id', '!=', $operationLog->id) // استثناء السجل الحالي
                ->when($generationUnitId, function($query) use ($generationUnitId) {
                    return $query->where('generators.generation_unit_id', $generationUnitId);
                })
                ->max('operation_logs.sequence') ?? 0;
            $data['sequence'] = $lastSequence + 1;
        }

        // Always calculate fuel consumed from start and end (ignore user input for security)
        // المعادلة: كمية الوقود المستهلك (لتر) = قراءة عداد الوقود عند الانتهاء - قراءة عداد الوقود عند البدء
        if (isset($data['fuel_meter_start']) && isset($data['fuel_meter_end']) 
            && $data['fuel_meter_start'] > 0 && $data['fuel_meter_end'] > 0 
            && $data['fuel_meter_end'] >= $data['fuel_meter_start']) {
            $data['fuel_consumed'] = round($data['fuel_meter_end'] - $data['fuel_meter_start'], 2);
        } else {
            $data['fuel_consumed'] = null;
        }

        // Always calculate energy produced from start and end (ignore user input for security)
        // المعادلة: كمية الطاقة المنتجة (kWh) = قراءة عداد الطاقة عند الإيقاف - قراءة عداد الطاقة عند البدء
        if (isset($data['energy_meter_start']) && isset($data['energy_meter_end']) 
            && $data['energy_meter_start'] > 0 && $data['energy_meter_end'] > 0 
            && $data['energy_meter_end'] >= $data['energy_meter_start']) {
            $data['energy_produced'] = round($data['energy_meter_end'] - $data['energy_meter_start'], 2);
        } else {
            $data['energy_produced'] = null;
        }

        // Auto-fill electricity tariff price if not provided
        if (!isset($data['electricity_tariff_price']) || empty($data['electricity_tariff_price'])) {
            $operationDate = Carbon::parse($data['operation_date']);
            $tariffPrice = \App\Models\ElectricityTariffPrice::getActivePriceForDate(
                $data['operator_id'],
                $operationDate
            );
            
            if ($tariffPrice) {
                $data['electricity_tariff_price'] = $tariffPrice->price_per_kwh;
            }
        }

        $operationLog->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سجل التشغيل بنجاح.',
            ]);
        }

        return redirect()->route('admin.operation-logs.index')
            ->with('success', 'تم تحديث سجل التشغيل بنجاح.');
    }

    /**
     * Remove the specified operation log record from database.
     */
    public function destroy(Request $request, OperationLog $operationLog): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $operationLog);

        $operationLog->delete();

        $msg = 'تم حذف سجل التشغيل بنجاح.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.operation-logs.index')->with('success', $msg);
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
