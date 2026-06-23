<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenerationUnit;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {}

    public function index()
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

        // ===== لوحة الفوترة والتحصيل (مفعّلة — مقسّمة حسب الدور) =====
        // الـ scoping محصّن داخل DashboardService::getBillingDashboard():
        //   - SuperAdmin/Admin/EnergyAuthority/GeneralAccountant → كل المشغلين
        //   - CompanyOwner/Employee (بصلاحية فوترة) → مشغله(م) فقط
        //   - تقني/دفاع مدني أو بلا صلاحية فوترة → لا يرى القسم
        $billingDashboard = null;
        $canSeeBilling = $user->hasGlobalAccountingAccess()
            || $user->isCompanyOwner()
            || $user->hasPermission('invoice_reports.view')
            || $user->hasPermission('subscriber_accounts.view');
        if ($canSeeBilling) {
            $billingDashboard = $this->service->getBillingDashboard($operatorIds);
        }

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
            'billingDashboard'
        ));
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
