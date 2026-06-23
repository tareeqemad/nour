<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenerationUnit;
use App\Models\Operator;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        // التحقق من اكتمال بيانات المشغل
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                $isComplete = !empty($operator->name)
                    && !empty($operator->owner_name)
                    && !empty($operator->owner_id_number)
                    && !empty($operator->operator_id_number);
                if ($operator->profile_completed != $isComplete) {
                    $operator->update(['profile_completed' => $isComplete]);
                    $operator->refresh();
                }
                if (!$operator->isProfileComplete()) {
                    return redirect()->route('admin.operators.profile')
                        ->with('warning', 'يرجى إكمال بيانات المشغل أولاً.');
                }
            }
        }

        // مستخدم بلا لوحة تحكم خاصة بدوره (مثل "مراقب عام") لكنه يملك صلاحية التقارير
        // → وجّهه مباشرة لتقارير الفوترة (مكان عمله) بدل لوحة فارغة
        $hasRoleDashboard = $user->isSuperAdmin() || $user->isEnergyAuthority() || $user->isAdmin()
            || $user->isGeneralAccountant() || $user->isCompanyOwner()
            || $user->isAffiliatedWithOperator() || $user->isCivilDefense();
        if (! $hasRoleDashboard && $user->hasPermission('invoice_reports.view')) {
            return redirect()->route('admin.invoice-reports.index');
        }

        // تحديد النطاق حسب نوع المستخدم
        $operatorIds = $this->service->getOperatorIds($user);
        $generatorIds = $this->service->getGeneratorIds($user, $operatorIds);

        // الإحصائيات الأساسية (cached)
        $stats = $this->service->getStatsForRole($user, $operatorIds, $generatorIds);
        $operationStats = $this->service->getOperationStats($operatorIds, $generatorIds);
        $maintenanceStats = $this->service->getMaintenanceStats($generatorIds);
        $fuelStats = $this->service->getFuelStats($generatorIds);
        $complianceStats = $this->service->getComplianceStats($operatorIds);

        // بيانات المهام (للفني والدفاع المدني)
        $tasksData = $this->service->getTasksData($user);

        // آخر العناصر والتنبيهات
        $recentData = $this->service->getRecentItems($user, $operatorIds, $generatorIds);
        $recentGenerators = $recentData['recentGenerators'];
        $recentOperators = $recentData['recentOperators'];
        $recentOperationLogs = $recentData['recentOperationLogs'];
        $generatorsNeedingMaintenance = $recentData['generatorsNeedingMaintenance'];
        $expiringCompliance = $recentData['expiringCompliance'];

        // إشعارات (مع cache guard)
        $this->service->createNotificationsIfNeeded($user, $generatorsNeedingMaintenance, $expiringCompliance);

        // توضيح لسلطة الطاقة
        $showEmptyDataHint = false;
        if ($user->isEnergyAuthority() && !empty($stats)) {
            $hasStructure = ($stats['operators']['total'] ?? 0) > 0
                || ($stats['generation_units']['total'] ?? 0) > 0
                || ($stats['generators']['total'] ?? 0) > 0;
            $noProduction = (($stats['production']['total_energy'] ?? 0) == 0)
                && (($stats['production']['total_fuel'] ?? 0) == 0);
            $noCompliance = ($stats['compliance']['total'] ?? 0) == 0;
            $showEmptyDataHint = $hasStructure && $noProduction && $noCompliance;
        }

        // بيانات مقارنة المشغلين (للعرض المباشر بدون AJAX لسلطة الطاقة — energy-authority operations-statistics charts)
        $operatorsComparison = null;
        $generationUnitsComparison = null;

        // ===== لوحة الفوترة والتحصيل (مفعّلة — حسب الدور، مع اختيار المشغّل والفترة) =====
        $billing                 = $this->resolveBillingContext($user, $request);
        $billingDashboard        = $billing['billingDashboard'];
        $billingIsGlobal         = $billing['isGlobal'];
        $billingOperators        = $billing['operators'];
        $billingSelectedOperator = $billing['selectedOperator'];
        $billingRange            = $billing['range'];
        $billingFrom             = $billing['from'];
        $billingTo               = $billing['to'];

        return view('admin.dashboard', compact(
            'stats',
            'operationStats',
            'maintenanceStats',
            'fuelStats',
            'complianceStats',
            'recentGenerators',
            'recentOperators',
            'recentOperationLogs',
            'generatorsNeedingMaintenance',
            'expiringCompliance',
            'tasksData',
            'operatorsComparison',
            'generationUnitsComparison',
            'showEmptyDataHint',
            'billingDashboard',
            'billingIsGlobal',
            'billingOperators',
            'billingSelectedOperator',
            'billingRange',
            'billingFrom',
            'billingTo'
        ));
    }

    /**
     * يحلّ سياق لوحة الفوترة: البوابة حسب الدور + اختيار المشغّل (للأدوار العامة) + الفترة.
     * النطاق محصّن داخل DashboardService (مالك/موظف لا يرى إلا مشغّله مهما أرسل).
     */
    private function resolveBillingContext($user, Request $request): array
    {
        $isGlobal = $user->hasGlobalAccountingAccess();

        $canSeeBilling = $isGlobal
            || $user->isCompanyOwner()
            || $user->hasPermission('invoice_reports.view')
            || $user->hasPermission('subscriber_accounts.view');

        // الفترة الزمنية (presets + مخصّص)
        $range = $request->input('bill_range', 'month');
        [$from, $to] = match ($range) {
            'quarter' => [now()->subMonths(2)->startOfMonth()->toDateString(), now()->toDateString()],
            'half'    => [now()->subMonths(5)->startOfMonth()->toDateString(), now()->toDateString()],
            'year'    => [now()->startOfYear()->toDateString(), now()->toDateString()],
            'all'     => [null, null],
            'custom'  => [$request->input('bill_from') ?: null, $request->input('bill_to') ?: null],
            default   => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        };
        if (! in_array($range, ['month', 'quarter', 'half', 'year', 'all', 'custom'], true)) {
            $range = 'month';
        }

        // اختيار مشغّل محدّد — للأدوار العامة فقط؛ غيرهم محصور بنطاقه
        $selectedOperator = null;
        if ($isGlobal && $request->filled('bill_operator')) {
            $opId = (int) $request->input('bill_operator');
            if ($opId > 0 && Operator::whereKey($opId)->exists()) {
                $selectedOperator = $opId;
            }
        }

        $scopeOperatorIds = $selectedOperator
            ? [$selectedOperator]
            : $this->service->getOperatorIds($user);

        $billingDashboard = $canSeeBilling
            ? $this->service->getBillingDashboard($scopeOperatorIds, $from, $to)
            : null;

        $operators = $isGlobal
            ? Operator::orderBy('name')->get(['id', 'name'])
            : collect();

        return [
            'billingDashboard' => $billingDashboard,
            'isGlobal'         => $isGlobal,
            'operators'        => $operators,
            'selectedOperator' => $selectedOperator,
            'range'            => $range,
            'from'             => $from,
            'to'               => $to,
        ];
    }

    /**
     * AJAX: إعادة تحميل قسم الفوترة فقط (عند تغيير المشغّل أو الفترة).
     */
    public function billingData(Request $request)
    {
        $user = auth()->user();
        $billing = $this->resolveBillingContext($user, $request);

        if (! $billing['billingDashboard']) {
            return response()->json(['html' => '']);
        }

        $html = view('admin.dashboard.partials.billing-content', [
            'billingDashboard'        => $billing['billingDashboard'],
            'billingSelectedOperator' => $billing['selectedOperator'],
            'billingRange'            => $billing['range'],
            'billingFrom'             => $billing['from'],
            'billingTo'               => $billing['to'],
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * AJAX: بيانات المخططات البيانية (آخر 30 يوم)
     */
    public function chartData()
    {
        $user = auth()->user();
        $operatorIds = $this->service->getOperatorIds($user);
        $generatorIds = $this->service->getGeneratorIds($user, $operatorIds);

        return response()->json($this->service->getChartData($operatorIds, $generatorIds));
    }

    /**
     * AJAX: بيانات المخطط الدائري
     */
    public function pieChartData()
    {
        $user = auth()->user();
        $operatorIds = $this->service->getOperatorIds($user);
        $generatorIds = $this->service->getGeneratorIds($user, $operatorIds);

        return response()->json($this->service->getPieChartData($operatorIds, $generatorIds));
    }

    /**
     * AJAX: مقارنة المشغلين — HTML partial
     */
    public function operatorsComparison()
    {
        $user = auth()->user();
        if (!$user->isEnergyAuthority() && !$user->isSuperAdmin()) {
            abort(403);
        }

        $operatorsComparison = $this->service->getOperatorsComparisonData();

        return view('admin.dashboard.partials.operators-comparison-content', compact('operatorsComparison'));
    }

    /**
     * AJAX: مقارنة وحدات التوليد — HTML partial
     */
    public function generationUnitsComparison()
    {
        $user = auth()->user();
        if (!$user->isEnergyAuthority() && !$user->isSuperAdmin()) {
            abort(403);
        }

        $generationUnitsComparison = $this->service->getGenerationUnitsComparisonData();

        return view('admin.dashboard.partials.generation-units-comparison-content', compact('generationUnitsComparison'));
    }
}
