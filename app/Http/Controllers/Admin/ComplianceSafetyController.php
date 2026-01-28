<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComplianceSafetyRequest;
use App\Http\Requests\Admin\UpdateComplianceSafetyRequest;
use App\Models\ComplianceSafety;
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
        $query = ComplianceSafety::with(['operator', 'creator']);

        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $query->where('operator_id', $operator->id);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
            $query->whereIn('operator_id', $operators->pluck('id'));
        }

        // Search
        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $query->whereHas('operator', function ($oq) use ($q) {
                $oq->where('name', 'like', "%{$q}%");
            })
            ->orWhere('inspection_authority', 'like', "%{$q}%")
            ->orWhere('inspection_result', 'like', "%{$q}%")
            ->orWhere('violations', 'like', "%{$q}%");
        }

        // Filter by operator
        $operatorId = (int) $request->input('operator_id', 0);
        if ($operatorId > 0) {
            $query->where('operator_id', $operatorId);
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
        
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            $operators = Operator::select('id', 'name')
                ->orderBy('name')
                ->get();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
            }
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $operators = $user->operators;
        }

        return view('admin.compliance-safeties.index', compact('complianceSafeties', 'operators', 'groupedLogs'));
    }

    /**
     * Show form for creating a new compliance safety record entry.
     */
    public function create(): View
    {
        $this->authorize('create', ComplianceSafety::class);

        $user = auth()->user();
        $operators = collect();

        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
        }
        
        // جلب ثوابت حالة شهادة السلامة
        $constants = [
            'safety_certificate_status' => \App\Helpers\ConstantsHelper::get(13), // حالة شهادة السلامة
        ];

        return view('admin.compliance-safeties.create', compact('operators', 'constants'));
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

        $complianceSafety = ComplianceSafety::create($data);
        $complianceSafety->load('operator');
        
        $operator = $complianceSafety->operator;
        $user = auth()->user();
        
        if ($operator) {
            // إشعار للمشغل
            Notification::notifyOperatorUsers(
                $operator,
                'compliance_added',
                'تم إضافة سجل امتثال وسلامة',
                "تم إضافة سجل امتثال وسلامة للمشغل: {$operator->name} من قبل: {$user->name}",
                route('admin.compliance-safeties.show', $complianceSafety)
            );
            
            // إشعار لسلطة الطاقة والسوبر ادمن والادمن إذا كان من دفاع مدني
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

        $complianceSafety->load([
            'operator',
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

        if ($user->isSuperAdmin()) {
            $operators = Operator::select('id', 'name')->orderBy('name')->get();
        } elseif ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $operators = collect([$operator]);
            }
        } elseif ($user->isEmployee()) {
            $operators = $user->operators;
        }
        
        // جلب ثوابت حالة شهادة السلامة
        $constants = [
            'safety_certificate_status' => \App\Helpers\ConstantsHelper::get(13), // حالة شهادة السلامة
        ];

        return view('admin.compliance-safeties.edit', compact('complianceSafety', 'operators', 'constants'));
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
}
