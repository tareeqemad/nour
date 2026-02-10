<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComplianceSafetyRequest;
use App\Http\Requests\Admin\UpdateComplianceSafetyRequest;
use App\Models\ComplianceSafety;
use App\Models\Generator;
use App\Models\GenerationUnit;
use App\Models\Notification;
use App\Models\Operator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplianceSafetyController extends Controller
{
    /**
     * Display paginated list of compliance safety records filtered by user role.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', ComplianceSafety::class);

        $user = auth()->user();
        $query = ComplianceSafety::with(['operator', 'generationUnit', 'generator', 'creator']);

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $query->whereIn('operator_id', $operators->pluck('id'));
        }

        // Filter by operator
        $operatorId = (int) $request->input('operator_id', 0);
        $generationUnitId = (int) $request->input('generation_unit_id', 0);
        $generatorId = (int) $request->input('generator_id', 0);

        // If generation unit or generator selected, resolve operator (compliance records are operator-scoped only)
        if ($generatorId > 0) {
            $gen = Generator::find($generatorId);
            if ($gen) {
                $operatorId = $operatorId ?: (int) $gen->operator_id;
            }
        }
        if ($generationUnitId > 0) {
            $unit = GenerationUnit::find($generationUnitId);
            if ($unit) {
                $operatorId = $operatorId ?: (int) $unit->operator_id;
            }
        }
        if ($operatorId > 0) {
            $query->where('operator_id', $operatorId);
        }
        if ($generationUnitId > 0) {
            $query->where('generation_unit_id', $generationUnitId);
        }
        if ($generatorId > 0) {
            $query->where('generator_id', $generatorId);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('last_inspection_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('last_inspection_date', '<=', $request->input('date_to'));
        }

        // Paginate - 100 items per page
        $complianceSafeties = $query->latest('last_inspection_date')->paginate(100);

        if ($request->ajax() || $request->wantsJson()) {
            // Return tbody rows only (for normal table view)
            $html = view('admin.compliance-safeties.partials.tbody-rows', compact('complianceSafeties'))->render();
            $pagination = view('admin.compliance-safeties.partials.pagination', compact('complianceSafeties'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination,
                'count' => $complianceSafeties->total(),
            ]);
        }

        // For normal page load, check if group_by_operator is requested
        $groupByOperator = $request->boolean('group_by_operator', false);
        $groupedLogs = null;
        if ($groupByOperator) {
            $groupedLogs = $complianceSafeties->groupBy('operator_id');
        }

        $operators = collect();
        $generationUnits = collect();
        $generators = collect();

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
            $selectedOperatorId = (int) $request->input('operator_id', 0);
            if ($selectedOperatorId > 0) {
                $generationUnits = GenerationUnit::where('operator_id', $selectedOperatorId)
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
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
            }
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
            $generationUnits = GenerationUnit::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('unit_code')
                ->get();
            $generators = Generator::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                ->orderBy('generator_number')
                ->get();
        }

        return view('admin.compliance-safeties.index', compact('complianceSafeties', 'operators', 'generationUnits', 'generators', 'groupedLogs'));
    }

    /**
     * Show form for creating a new compliance safety record entry.
     */
    public function create(): View
    {
        $this->authorize('create', ComplianceSafety::class);

        $user = auth()->user();
        $operators = collect();
        $generationUnits = collect();
        $generators = collect();

        if ($user->isSuperAdmin() || $user->isAdmin() || $user->isEnergyAuthority()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
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
            $generationUnits = GenerationUnit::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('unit_code')
                ->get();
            $generators = Generator::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                ->orderBy('generator_number')
                ->get();
        }

        $constants = [
            'safety_certificate_status' => \App\Helpers\ConstantsHelper::get(13),
        ];

        return view('admin.compliance-safeties.create', compact('operators', 'generationUnits', 'generators', 'constants'));
    }

    /**
     * Store newly created compliance safety record in database.
     */
    public function store(StoreComplianceSafetyRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', ComplianceSafety::class);

        $data = $request->validated();

        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        // When generator_id is set, ensure operator_id and generation_unit_id match the generator
        if (!empty($data['generator_id'])) {
            $generator = Generator::find($data['generator_id']);
            if ($generator) {
                $data['operator_id'] = $generator->operator_id;
                $data['generation_unit_id'] = $generator->generation_unit_id;
            }
        }

        $complianceSafety = ComplianceSafety::create($data);
        $complianceSafety->load('operator');
        
        $operator = $complianceSafety->operator;
        $user = auth()->user();
        
        if ($operator) {
            // Notify operator users
            Notification::notifyOperatorUsers(
                $operator,
                'compliance_added',
                'تم إضافة سجل امتثال وسلامة',
                "تم إضافة سجل امتثال وسلامة للمشغل: {$operator->name} من قبل: {$user->name}",
                route('admin.compliance-safeties.show', $complianceSafety)
            );
            
            // Notify energy authority / super admin / admin when submitted by civil defense
            if ($user->isTechnician() || $user->isCivilDefense()) {
                Notification::notifyOperatorApprovers(
                    'compliance_added',
                    'تم إضافة سجل امتثال وسلامة',
                    "تم إضافة سجل امتثال وسلامة للمشغل: {$operator->name} من قبل: {$user->name}",
                    route('admin.compliance-safeties.show', $complianceSafety)
                );
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء سجل الامتثال والسلامة بنجاح.',
            ]);
        }

        return redirect()->route('admin.compliance-safeties.index')
            ->with('success', 'تم إنشاء سجل الامتثال والسلامة بنجاح.');
    }

    /**
     * Display detailed information about the specified compliance safety record.
     */
    public function show(ComplianceSafety $complianceSafety): View
    {
        $this->authorize('view', $complianceSafety);

        // Avoid showing any stale session('info') alert when loading this page
        session()->forget('info');

        $complianceSafety->load([
            'operator',
            'generationUnit',
            'generator',
            'safetyCertificateStatusDetail',
            'creator'
        ]);

        return view('admin.compliance-safeties.show', compact('complianceSafety'));
    }

    /**
     * Show form for editing the specified compliance safety record.
     */
    public function edit(ComplianceSafety $complianceSafety): View
    {
        $this->authorize('update', $complianceSafety);

        $user = auth()->user();
        $operators = collect();
        $generationUnits = collect();
        $generators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name', 'unit_number')->orderBy('name')->get();
            if ($complianceSafety->operator_id) {
                $generationUnits = GenerationUnit::where('operator_id', $complianceSafety->operator_id)
                    ->select('id', 'name', 'unit_code', 'operator_id')
                    ->orderBy('unit_code')
                    ->get();
                if ($complianceSafety->generation_unit_id) {
                    $generators = Generator::where('generation_unit_id', $complianceSafety->generation_unit_id)
                        ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                        ->orderBy('generator_number')
                        ->get();
                } else {
                    $generators = Generator::where('operator_id', $complianceSafety->operator_id)
                        ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                        ->orderBy('generator_number')
                        ->get();
                }
            }
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
            $generationUnits = GenerationUnit::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'unit_code', 'operator_id')
                ->orderBy('unit_code')
                ->get();
            $generators = Generator::whereIn('operator_id', $userOperators->pluck('id'))
                ->select('id', 'name', 'generator_number', 'operator_id', 'generation_unit_id')
                ->orderBy('generator_number')
                ->get();
        }

        $constants = [
            'safety_certificate_status' => \App\Helpers\ConstantsHelper::get(13),
        ];

        return view('admin.compliance-safeties.edit', compact('complianceSafety', 'operators', 'generationUnits', 'generators', 'constants'));
    }

    /**
     * Update the specified compliance safety record data in database.
     */
    public function update(UpdateComplianceSafetyRequest $request, ComplianceSafety $complianceSafety): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $complianceSafety);

        $data = $request->validated();

        if (auth()->user()->isCompanyOwner()) {
            $operator = auth()->user()->ownedOperators()->first();
            if ($operator) {
                $data['operator_id'] = $operator->id;
            }
        }

        // When generator_id is set, ensure operator_id and generation_unit_id match the generator
        if (!empty($data['generator_id'])) {
            $generator = Generator::find($data['generator_id']);
            if ($generator) {
                $data['operator_id'] = $generator->operator_id;
                $data['generation_unit_id'] = $generator->generation_unit_id;
            }
        }

        $complianceSafety->update($data);

        $complianceSafety->load('operator');
        if ($complianceSafety->operator) {
            Notification::notifyOperatorUsers(
                $complianceSafety->operator,
                'compliance_updated',
                'تم تحديث سجل امتثال وسلامة',
                "تم تحديث سجل امتثال وسلامة للمشغل: {$complianceSafety->operator->name}",
                route('admin.compliance-safeties.show', $complianceSafety)
            );
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث سجل الامتثال والسلامة بنجاح.',
            ]);
        }

        return redirect()->route('admin.compliance-safeties.index')
            ->with('success', 'تم تحديث سجل الامتثال والسلامة بنجاح.');
    }

    /**
     * Remove the specified compliance safety record from database.
     */
    public function destroy(Request $request, ComplianceSafety $complianceSafety): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $complianceSafety);

        $complianceSafety->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف سجل الامتثال والسلامة بنجاح.',
            ]);
        }

        return redirect()->route('admin.compliance-safeties.index')
            ->with('success', 'تم حذف سجل الامتثال والسلامة بنجاح.');
    }

    /**
     * Get generation units for the given operator (AJAX for cascading select).
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
                    'unit_code' => $unit->unit_code ?? '',
                    'unit_number' => $unit->unit_number ?? '',
                    'label' => $unit->unit_code ? "{$unit->name} ({$unit->unit_code})" : $unit->name,
                ];
            });

        return response()->json([
            'success' => true,
            'generation_units' => $generationUnits,
        ]);
    }

    /**
     * Get generators for the given generation unit (AJAX for cascading select).
     */
    public function getGenerators(Request $request, GenerationUnit $generationUnit): JsonResponse
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
                    'label' => "{$generator->generator_number} — {$generator->name}",
                ];
            });

        return response()->json([
            'success' => true,
            'generators' => $generators,
        ]);
    }
}
