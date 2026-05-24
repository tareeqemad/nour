<?php

namespace App\Services;

use App\Models\ComplianceSafety;
use App\Models\ConstantDetail;
use App\Models\FuelEfficiency;
use App\Models\GenerationUnit;
use App\Models\Generator;
use App\Models\MaintenanceRecord;
use App\Models\Notification;
use App\Models\OperationLog;
use App\Models\Operator;
use App\Models\Task;
use App\Models\User;
use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private const STATS_TTL = 900;      // 15 minutes
    private const CHARTS_TTL = 300;     // 5 minutes
    private const COMPARISON_TTL = 900; // 15 minutes

    /**
     * تحديد نطاق المشغلين حسب نوع المستخدم
     */
    public function getOperatorIds($user): ?array
    {
        if ($user->hasGlobalAccountingAccess()) {
            return null;
        } elseif ($user->isCompanyOwner()) {
            return $user->ownedOperators->pluck('id')->toArray();
        } elseif ($user->isAffiliatedWithOperator() || $user->isCivilDefense()) {
            // يشمل: Employee, Technician, CivilDefense, وأي دور مخصص (custom role) مرتبط بمشغل
            return $user->getScopedOperatorIds();
        }
        return [];
    }

    /**
     * تحديد نطاق المولدات حسب نوع المستخدم
     */
    public function getGeneratorIds($user, ?array $operatorIds): ?array
    {
        if ($user->hasGlobalAccountingAccess()) {
            return null;
        }
        if ($operatorIds) {
            $generationUnitIds = GenerationUnit::whereIn('operator_id', $operatorIds)->pluck('id');
            return Generator::whereIn('generation_unit_id', $generationUnitIds)->pluck('id')->toArray();
        }
        return [];
    }

    /**
     * إحصائيات حسب الدور — مع cache
     */
    public function getStatsForRole($user, ?array $operatorIds = null, ?array $generatorIds = null): array
    {
        $cacheKey = 'dashboard_stats_' . $user->id;

        return Cache::remember($cacheKey, self::STATS_TTL, function () use ($user, $operatorIds, $generatorIds) {
            if ($user->isSuperAdmin()) {
                return $this->getSuperAdminStats();
            } elseif ($user->isEnergyAuthority()) {
                return $this->getEnergyAuthorityStats();
            } elseif ($user->isAdmin() || $user->isGeneralAccountant()) {
                return $this->getAdminStats();
            } elseif ($user->isCompanyOwner()) {
                return $this->getCompanyOwnerStats($user);
            } elseif ($user->isAffiliatedWithOperator()) {
                // يشمل: Employee, Technician, وأي دور مخصص (custom role) مرتبط بمشغل
                return $this->getEmployeeStats($user);
            } elseif ($user->isCivilDefense()) {
                return $this->getCivilDefenseStats($user);
            }
            return [];
        });
    }

    private function getSuperAdminStats(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'super_admins' => User::where('role', Role::SuperAdmin)->count(),
                'company_owners' => User::where('role', Role::CompanyOwner)->count(),
                'employees' => User::where('role', Role::Employee)->count(),
            ],
            'operators' => [
                'total' => Operator::count(),
                'active' => Operator::where('status', 'active')->count(),
            ],
            'generators' => [
                'total' => Generator::count(),
                'active' => Generator::whereHas('statusDetail', fn($q) => $q->where('code', 'ACTIVE'))->count(),
            ],
        ];
    }

    private function getAdminStats(): array
    {
        return [
            'operators' => [
                'total' => Operator::count(),
                'active' => Operator::where('status', 'active')->count(),
            ],
            'generators' => [
                'total' => Generator::count(),
                'active' => Generator::whereHas('statusDetail', fn($q) => $q->where('code', 'ACTIVE'))->count(),
            ],
            'company_owners' => [
                'total' => User::where('role', Role::CompanyOwner)->count(),
            ],
        ];
    }

    /**
     * إحصائيات سلطة الطاقة — محسّنة بعدد أقل من queries
     */
    private function getEnergyAuthorityStats(): array
    {
        // Entity counts
        $totalOperators = Operator::count();
        $activeOperators = Operator::where('status', 'active')->count();
        $approvedOperators = Operator::where('is_approved', true)->count();
        $pendingOperators = Operator::where('is_approved', false)->count();

        $totalGenerationUnits = GenerationUnit::count();
        $activeGenerationUnits = GenerationUnit::whereHas('statusDetail', fn($q) => $q->where('code', 'ACTIVE'))->count();

        $totalGenerators = Generator::count();
        $activeGenerators = Generator::whereHas('statusDetail', fn($q) => $q->where('code', 'ACTIVE'))->count();

        // Production aggregates — single query
        $production = OperationLog::select(
            DB::raw('SUM(energy_produced) as total_energy'),
            DB::raw('SUM(fuel_consumed) as total_fuel'),
            DB::raw('SUM(CASE WHEN MONTH(operation_date) = ? AND YEAR(operation_date) = ? THEN energy_produced ELSE 0 END) as this_month_energy'),
            DB::raw('SUM(CASE WHEN MONTH(operation_date) = ? AND YEAR(operation_date) = ? THEN fuel_consumed ELSE 0 END) as this_month_fuel')
        )->addBinding([now()->month, now()->year, now()->month, now()->year], 'select')
         ->first();

        $totalEnergy = (float) ($production->total_energy ?? 0);
        $totalFuel = (float) ($production->total_fuel ?? 0);
        $avgEfficiency = $totalFuel > 0 ? round($totalEnergy / $totalFuel, 2) : 0;

        // Capacity aggregates — single query
        $capacity = DB::table('generation_units')
            ->select(
                DB::raw('SUM(total_capacity) as total_unit_capacity'),
                DB::raw('SUM(beneficiaries_count) as total_beneficiaries')
            )->first();

        $totalGeneratorCapacity = Generator::sum('capacity_kva') ?? 0;

        // Compliance counts — single query with conditional
        $compliance = ComplianceSafety::select(
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN cd.code = \'VALID\' THEN 1 ELSE 0 END) as valid_count'),
            DB::raw('SUM(CASE WHEN cd.code = \'EXPIRED\' THEN 1 ELSE 0 END) as expired_count')
        )->join('constant_details as cd', 'compliance_safeties.safety_certificate_status_id', '=', 'cd.id')
         ->first();

        // Maintenance aggregates — single query
        $maintenance = MaintenanceRecord::select(
            DB::raw('SUM(CASE WHEN MONTH(maintenance_date) = ' . now()->month . ' AND YEAR(maintenance_date) = ' . now()->year . ' THEN 1 ELSE 0 END) as this_month'),
            DB::raw('SUM(downtime_hours) as total_downtime'),
            DB::raw('SUM(maintenance_cost) as total_cost')
        )->first();

        return [
            'operators' => [
                'total' => $totalOperators,
                'active' => $activeOperators,
                'approved' => $approvedOperators,
                'pending' => $pendingOperators,
            ],
            'generation_units' => [
                'total' => $totalGenerationUnits,
                'active' => $activeGenerationUnits,
            ],
            'generators' => [
                'total' => $totalGenerators,
                'active' => $activeGenerators,
            ],
            'production' => [
                'total_energy' => round($totalEnergy, 2),
                'total_fuel' => round($totalFuel, 2),
                'this_month_energy' => round((float) ($production->this_month_energy ?? 0), 2),
                'this_month_fuel' => round((float) ($production->this_month_fuel ?? 0), 2),
                'avg_efficiency' => $avgEfficiency,
            ],
            'capacity' => [
                'total_unit_capacity' => round((float) ($capacity->total_unit_capacity ?? 0), 2),
                'total_generator_capacity' => round((float) $totalGeneratorCapacity, 2),
                'total_beneficiaries' => (int) ($capacity->total_beneficiaries ?? 0),
            ],
            'compliance' => [
                'total' => (int) ($compliance->total ?? 0),
                'valid' => (int) ($compliance->valid_count ?? 0),
                'expired' => (int) ($compliance->expired_count ?? 0),
            ],
            'maintenance' => [
                'this_month' => (int) ($maintenance->this_month ?? 0),
                'total_downtime' => round((float) ($maintenance->total_downtime ?? 0), 2),
                'total_cost' => round((float) ($maintenance->total_cost ?? 0), 2),
            ],
        ];
    }

    private function getCompanyOwnerStats($user): array
    {
        $ownedOperators = $user->ownedOperators;
        $operatorIdsArray = $ownedOperators->pluck('id')->toArray();

        $generationUnitsTotal = GenerationUnit::whereIn('operator_id', $operatorIdsArray)->count();
        $generationUnitsActive = GenerationUnit::whereIn('operator_id', $operatorIdsArray)
            ->whereHas('statusDetail', fn($q) => $q->where('code', 'ACTIVE'))->count();

        return [
            'generation_units' => [
                'total' => $generationUnitsTotal,
                'active' => $generationUnitsActive,
            ],
            'operators' => [
                'total' => $ownedOperators->count(),
                'active' => $ownedOperators->where('status', 'active')->count(),
            ],
            'generators' => [
                'total' => Generator::whereHas('generationUnit', fn($q) => $q->whereIn('operator_id', $operatorIdsArray))->count(),
                'active' => Generator::whereHas('generationUnit', fn($q) => $q->whereIn('operator_id', $operatorIdsArray))
                    ->whereHas('statusDetail', fn($q) => $q->where('code', 'ACTIVE'))->count(),
            ],
            'employees' => [
                'total' => User::whereHas('operators', fn($q) => $q->whereIn('operators.id', $operatorIdsArray))
                    ->where('role', Role::Employee)->distinct()->count(),
            ],
        ];
    }

    private function getEmployeeStats($user): array
    {
        $userOperators = $user->operators;

        return [
            'operators' => [
                'total' => $userOperators->count(),
            ],
            'generators' => [
                'total' => Generator::whereHas('generationUnit', fn($q) => $q->whereIn('operator_id', $userOperators->pluck('id')))->count(),
                'active' => Generator::whereHas('generationUnit', fn($q) => $q->whereIn('operator_id', $userOperators->pluck('id')))
                    ->whereHas('statusDetail', fn($q) => $q->where('code', 'ACTIVE'))->count(),
            ],
        ];
    }

    private function getCivilDefenseStats($user): array
    {
        return [
            'tasks' => [
                'total' => Task::where('assigned_to', $user->id)->count(),
                'pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
                'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
                'completed' => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
            ],
        ];
    }

    /**
     * إحصائيات التشغيل — مع cache
     */
    public function getOperationStats(?array $operatorIds, ?array $generatorIds): array
    {
        $hash = md5(json_encode([$operatorIds, $generatorIds]));
        $cacheKey = "dashboard_ops_{$hash}";

        return Cache::remember($cacheKey, self::STATS_TTL, function () use ($operatorIds, $generatorIds) {
            $query = OperationLog::query();
            if ($operatorIds) $query->whereIn('operator_id', $operatorIds);
            if ($generatorIds) $query->whereIn('generator_id', $generatorIds);

            $result = $query->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN MONTH(operation_date) = ' . now()->month . ' AND YEAR(operation_date) = ' . now()->year . ' THEN 1 ELSE 0 END) as this_month'),
                DB::raw('SUM(CASE WHEN operation_date BETWEEN \'' . now()->startOfWeek()->toDateString() . '\' AND \'' . now()->endOfWeek()->toDateString() . '\' THEN 1 ELSE 0 END) as this_week'),
                DB::raw('SUM(energy_produced) as total_energy'),
                DB::raw('SUM(fuel_consumed) as total_fuel'),
                DB::raw('AVG(load_percentage) as avg_load')
            )->first();

            return [
                'total' => (int) ($result->total ?? 0),
                'this_month' => (int) ($result->this_month ?? 0),
                'this_week' => (int) ($result->this_week ?? 0),
                'total_energy' => round((float) ($result->total_energy ?? 0), 2),
                'total_fuel' => round((float) ($result->total_fuel ?? 0), 2),
                'avg_load' => round((float) ($result->avg_load ?? 0), 2),
            ];
        });
    }

    /**
     * إحصائيات الصيانة — مع cache
     */
    public function getMaintenanceStats(?array $generatorIds): array
    {
        $hash = md5(json_encode($generatorIds));

        return Cache::remember("dashboard_maintenance_{$hash}", self::STATS_TTL, function () use ($generatorIds) {
            $query = MaintenanceRecord::query();
            if ($generatorIds) $query->whereIn('generator_id', $generatorIds);

            $result = $query->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN MONTH(maintenance_date) = ' . now()->month . ' AND YEAR(maintenance_date) = ' . now()->year . ' THEN 1 ELSE 0 END) as this_month'),
                DB::raw('SUM(maintenance_cost) as total_cost'),
                DB::raw('SUM(downtime_hours) as total_downtime')
            )->first();

            return [
                'total' => (int) ($result->total ?? 0),
                'this_month' => (int) ($result->this_month ?? 0),
                'total_cost' => round((float) ($result->total_cost ?? 0), 2),
                'total_downtime' => round((float) ($result->total_downtime ?? 0), 2),
            ];
        });
    }

    /**
     * إحصائيات كفاءة الوقود
     */
    public function getFuelStats(?array $generatorIds): array
    {
        $hash = md5(json_encode($generatorIds));

        return Cache::remember("dashboard_fuel_{$hash}", self::STATS_TTL, function () use ($generatorIds) {
            $query = FuelEfficiency::query();
            if ($generatorIds) $query->whereIn('generator_id', $generatorIds);

            $result = $query->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(fuel_efficiency_percentage) as avg_fuel_efficiency'),
                DB::raw('AVG(energy_distribution_efficiency) as avg_energy_efficiency'),
                DB::raw('SUM(total_operating_cost) as total_cost')
            )->first();

            return [
                'total' => (int) ($result->total ?? 0),
                'avg_fuel_efficiency' => round((float) ($result->avg_fuel_efficiency ?? 0), 2),
                'avg_energy_efficiency' => round((float) ($result->avg_energy_efficiency ?? 0), 2),
                'total_cost' => round((float) ($result->total_cost ?? 0), 2),
            ];
        });
    }

    /**
     * إحصائيات الامتثال
     */
    public function getComplianceStats(?array $operatorIds): array
    {
        $hash = md5(json_encode($operatorIds));

        return Cache::remember("dashboard_compliance_{$hash}", self::STATS_TTL, function () use ($operatorIds) {
            $query = ComplianceSafety::query();
            if ($operatorIds) $query->whereIn('operator_id', $operatorIds);

            $total = (clone $query)->count();
            $valid = (clone $query)->whereHas('safetyCertificateStatusDetail', fn($q) => $q->where('code', 'VALID'))->count();
            $expired = (clone $query)->whereHas('safetyCertificateStatusDetail', fn($q) => $q->where('code', 'EXPIRED'))->count();
            $pending = (clone $query)->whereHas('safetyCertificateStatusDetail', fn($q) => $q->where('code', 'PENDING'))->count();

            return compact('total', 'valid', 'expired', 'pending');
        });
    }

    /**
     * بيانات المخططات (آخر 30 يوم) — AJAX endpoint
     */
    public function getChartData(?array $operatorIds, ?array $generatorIds): array
    {
        $hash = md5(json_encode([$operatorIds, $generatorIds]));

        return Cache::remember("dashboard_charts_{$hash}", self::CHARTS_TTL, function () use ($operatorIds, $generatorIds) {
            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now();

            $query = OperationLog::query();
            if ($operatorIds) $query->whereIn('operator_id', $operatorIds);
            if ($generatorIds) $query->whereIn('generator_id', $generatorIds);

            $data = $query->whereBetween('operation_date', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(operation_date) as date'),
                    DB::raw('SUM(energy_produced) as total_energy'),
                    DB::raw('SUM(fuel_consumed) as total_fuel'),
                    DB::raw('COUNT(*) as records_count'),
                    DB::raw('AVG(load_percentage) as avg_load')
                )
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy('date');

            $dates = [];
            $energyData = [];
            $fuelData = [];
            $recordsData = [];
            $loadData = [];

            for ($i = 30; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $dates[] = Carbon::now()->subDays($i)->format('d/m');
                $dayData = $data[$date] ?? null;
                $energyData[] = $dayData ? round((float) $dayData->total_energy, 2) : 0;
                $fuelData[] = $dayData ? round((float) $dayData->total_fuel, 2) : 0;
                $recordsData[] = $dayData ? (int) $dayData->records_count : 0;
                $loadData[] = $dayData ? round((float) $dayData->avg_load, 2) : 0;
            }

            // حساب الفاقد في الوقود
            $generatorsQuery = Generator::query();
            if ($generatorIds) {
                $generatorsQuery->whereIn('id', $generatorIds);
            } elseif ($operatorIds) {
                $generatorsQuery->whereIn('operator_id', $operatorIds);
            }

            $avgIdealEfficiency = $generatorsQuery->avg('ideal_fuel_efficiency') ?? 0.5;
            if ($avgIdealEfficiency <= 0) $avgIdealEfficiency = 0.5;

            $fuelLossData = [];
            foreach ($energyData as $index => $energy) {
                $fuelConsumed = $fuelData[$index];
                if ($energy > 0 && $avgIdealEfficiency > 0) {
                    $expectedFuel = $energy / $avgIdealEfficiency;
                    $fuelLoss = max(0, $fuelConsumed - $expectedFuel);
                } else {
                    $fuelLoss = 0;
                }
                $fuelLossData[] = round($fuelLoss, 2);
            }

            return [
                'labels' => $dates,
                'energy' => $energyData,
                'fuel' => $fuelData,
                'records' => $recordsData,
                'load' => $loadData,
                'advanced_chart' => [
                    'labels' => $dates,
                    'energy' => $energyData,
                    'fuel_consumed' => $fuelData,
                    'fuel_loss' => $fuelLossData,
                ],
            ];
        });
    }

    /**
     * بيانات المخطط الدائري — AJAX endpoint
     */
    public function getPieChartData(?array $operatorIds, ?array $generatorIds): array
    {
        $hash = md5(json_encode([$operatorIds, $generatorIds]));

        return Cache::remember("dashboard_pie_{$hash}", self::CHARTS_TTL, function () use ($operatorIds, $generatorIds) {
            $baseQuery = function () use ($operatorIds, $generatorIds) {
                $query = OperationLog::query();
                if ($operatorIds) $query->whereIn('operation_logs.operator_id', $operatorIds);
                if ($generatorIds) $query->whereIn('operation_logs.generator_id', $generatorIds);
                return $query;
            };

            // بيانات المولدات
            $generatorsData = (clone $baseQuery())
                ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
                ->select(
                    'generators.id', 'generators.name',
                    DB::raw('SUM(operation_logs.energy_produced) as total_energy'),
                    DB::raw('SUM(operation_logs.fuel_consumed) as total_fuel_consumed'),
                    DB::raw('AVG(operation_logs.load_percentage) as avg_load'),
                    DB::raw('COUNT(DISTINCT operation_logs.id) as records_count')
                )
                ->whereNotNull('operation_logs.energy_produced')
                ->groupBy('generators.id', 'generators.name')
                ->orderByDesc('total_energy')
                ->limit(10)
                ->get()
                ->map(fn($item) => $this->mapPieChartItem($item));

            // بيانات المشغلين
            $operatorsData = (clone $baseQuery())
                ->join('operators', 'operation_logs.operator_id', '=', 'operators.id')
                ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
                ->select(
                    'operators.id', 'operators.name',
                    DB::raw('SUM(operation_logs.energy_produced) as total_energy'),
                    DB::raw('SUM(operation_logs.fuel_consumed) as total_fuel_consumed'),
                    DB::raw('AVG(operation_logs.load_percentage) as avg_load'),
                    DB::raw('COUNT(DISTINCT operation_logs.id) as records_count')
                )
                ->whereNotNull('operation_logs.energy_produced')
                ->groupBy('operators.id', 'operators.name')
                ->orderByDesc('total_energy')
                ->limit(10)
                ->get()
                ->map(fn($item) => $this->mapPieChartItem($item));

            // بيانات المحافظات
            $governoratesData = (clone $baseQuery())
                ->join('operators', 'operation_logs.operator_id', '=', 'operators.id')
                ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
                ->join('generation_units', 'generators.generation_unit_id', '=', 'generation_units.id')
                ->join('constant_details as governorate_detail', 'generation_units.governorate_id', '=', 'governorate_detail.id')
                ->select(
                    'governorate_detail.value as governorate_value',
                    DB::raw('SUM(operation_logs.energy_produced) as total_energy'),
                    DB::raw('SUM(operation_logs.fuel_consumed) as total_fuel_consumed'),
                    DB::raw('AVG(operation_logs.load_percentage) as avg_load'),
                    DB::raw('COUNT(DISTINCT operation_logs.id) as records_count')
                )
                ->whereNotNull('operation_logs.energy_produced')
                ->whereNotNull('generation_units.governorate_id')
                ->groupBy('governorate_detail.value')
                ->orderByDesc('total_energy')
                ->get()
                ->map(function ($item) {
                    $governorateValue = (int) $item->governorate_value;
                    $governorate = \App\Governorate::tryFrom($governorateValue);
                    $base = $this->mapPieChartItem($item);
                    $base['id'] = $governorateValue;
                    $base['name'] = $governorate ? $governorate->label() : 'غير محدد';
                    $base['code'] = $governorate ? $governorate->code() : '';
                    return $base;
                });

            // إحصائيات إجمالية
            $totalStatsQuery = (clone $baseQuery())->whereNotNull('operation_logs.energy_produced');
            $totalEnergy = (float) ($totalStatsQuery->sum('energy_produced') ?? 0);
            $totalFuel = (float) ((clone $baseQuery())->whereNotNull('operation_logs.energy_produced')->sum('fuel_consumed') ?? 0);
            $totalRecords = (int) ((clone $baseQuery())->whereNotNull('operation_logs.energy_produced')->count() ?? 0);

            return [
                'generators' => [
                    'labels' => $generatorsData->pluck('name')->toArray(),
                    'data' => $generatorsData->pluck('energy')->toArray(),
                    'details' => $generatorsData->toArray(),
                ],
                'operators' => [
                    'labels' => $operatorsData->pluck('name')->toArray(),
                    'data' => $operatorsData->pluck('energy')->toArray(),
                    'details' => $operatorsData->toArray(),
                ],
                'governorates' => [
                    'labels' => $governoratesData->pluck('name')->toArray(),
                    'data' => $governoratesData->pluck('energy')->toArray(),
                    'details' => $governoratesData->toArray(),
                ],
                'total_stats' => [
                    'total_energy' => round($totalEnergy, 2),
                    'total_fuel' => round($totalFuel, 2),
                    'total_records' => $totalRecords,
                    'avg_efficiency' => $totalFuel > 0 ? round($totalEnergy / $totalFuel, 2) : 0,
                ],
            ];
        });
    }

    private function mapPieChartItem($item): array
    {
        $consumed = (float) $item->total_fuel_consumed;
        $energy = (float) $item->total_energy;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'energy' => round($energy, 2),
            'fuel_consumed' => round($consumed, 2),
            'fuel_efficiency' => $consumed > 0 ? round($energy / $consumed, 2) : 0,
            'avg_load' => round((float) $item->avg_load, 2),
            'records_count' => (int) $item->records_count,
        ];
    }

    /**
     * مقارنة المشغلين — N+1 fixed بـ aggregate query واحد
     */
    public function getOperatorsComparisonData(): array
    {
        return Cache::remember('dashboard_operators_comparison', self::COMPARISON_TTL, function () {
            $operators = Operator::with(['generationUnits', 'generators'])
                ->where('status', 'active')
                ->get();

            if ($operators->isEmpty()) return [];

            $operatorIds = $operators->pluck('id');
            $allGeneratorIds = $operators->flatMap(fn($o) => $o->generators->pluck('id'));

            // Aggregate all operation logs in ONE query
            $opLogs = OperationLog::whereIn('operator_id', $operatorIds)
                ->select(
                    'operator_id',
                    DB::raw('SUM(energy_produced) as total_energy'),
                    DB::raw('SUM(fuel_consumed) as total_fuel'),
                    DB::raw('COUNT(*) as operations_count'),
                    DB::raw('AVG(load_percentage) as avg_load'),
                    DB::raw('SUM(CASE WHEN MONTH(operation_date) = ' . now()->month . ' AND YEAR(operation_date) = ' . now()->year . ' THEN energy_produced ELSE 0 END) as this_month_energy'),
                    DB::raw('SUM(CASE WHEN MONTH(operation_date) = ' . now()->month . ' AND YEAR(operation_date) = ' . now()->year . ' THEN fuel_consumed ELSE 0 END) as this_month_fuel')
                )
                ->groupBy('operator_id')
                ->get()
                ->keyBy('operator_id');

            // Aggregate maintenance per generator, then map to operators in PHP
            $maintenancePerGenerator = MaintenanceRecord::whereIn('generator_id', $allGeneratorIds)
                ->select(
                    'generator_id',
                    DB::raw('COUNT(*) as maintenance_count'),
                    DB::raw('SUM(downtime_hours) as total_downtime')
                )
                ->groupBy('generator_id')
                ->get()
                ->keyBy('generator_id');

            // Build operator → maintenance mapping
            $maintenanceAgg = collect();
            foreach ($operators as $operator) {
                $genIds = $operator->generators->pluck('id');
                $count = 0;
                $downtime = 0;
                foreach ($genIds as $gId) {
                    if (isset($maintenancePerGenerator[$gId])) {
                        $count += $maintenancePerGenerator[$gId]->maintenance_count;
                        $downtime += (float) $maintenancePerGenerator[$gId]->total_downtime;
                    }
                }
                $maintenanceAgg[$operator->id] = (object) ['maintenance_count' => $count, 'total_downtime' => $downtime];
            }

            // Compliance per operator
            $complianceMap = ComplianceSafety::with('safetyCertificateStatusDetail')
                ->whereIn('operator_id', $operatorIds)
                ->get()
                ->groupBy('operator_id')
                ->map(fn($items) => $items->first()?->safetyCertificateStatusDetail?->code ?? 'UNKNOWN');

            return $operators->map(function ($operator) use ($opLogs, $maintenanceAgg, $complianceMap) {
                $log = $opLogs[$operator->id] ?? null;
                $maint = $maintenanceAgg[$operator->id] ?? null;

                $totalEnergy = (float) ($log->total_energy ?? 0);
                $totalFuel = (float) ($log->total_fuel ?? 0);
                $efficiency = $totalFuel > 0 ? round($totalEnergy / $totalFuel, 2) : 0;

                $installedCapacity = $operator->generators->sum('capacity_kva') ?? 0;
                $avgIdealEfficiency = $operator->generators->avg('ideal_fuel_efficiency') ?? 0.5;
                $expectedEnergy = $avgIdealEfficiency > 0 ? $totalFuel * $avgIdealEfficiency : 0;
                $energyLoss = max(0, $expectedEnergy - $totalEnergy);
                $lossPercentage = $expectedEnergy > 0 ? round(($energyLoss / $expectedEnergy) * 100, 2) : 0;

                return [
                    'id' => $operator->id,
                    'name' => $operator->name,
                    'status' => $operator->status,
                    'is_approved' => $operator->is_approved,
                    'generation_units_count' => $operator->generationUnits->count(),
                    'generators_count' => $operator->generators->count(),
                    'installed_capacity' => round($installedCapacity, 2),
                    'total_energy' => round($totalEnergy, 2),
                    'total_fuel' => round($totalFuel, 2),
                    'this_month_energy' => round((float) ($log->this_month_energy ?? 0), 2),
                    'this_month_fuel' => round((float) ($log->this_month_fuel ?? 0), 2),
                    'efficiency' => $efficiency,
                    'avg_load' => round((float) ($log->avg_load ?? 0), 2),
                    'operations_count' => (int) ($log->operations_count ?? 0),
                    'energy_loss' => round($energyLoss, 2),
                    'loss_percentage' => $lossPercentage,
                    'maintenance_count' => (int) ($maint->maintenance_count ?? 0),
                    'total_downtime' => round((float) ($maint->total_downtime ?? 0), 2),
                    'compliance_status' => $complianceMap[$operator->id] ?? 'UNKNOWN',
                ];
            })
                ->sortByDesc('total_energy')
                ->values()
                ->toArray();
        });
    }

    /**
     * مقارنة وحدات التوليد — N+1 fixed
     */
    public function getGenerationUnitsComparisonData(): array
    {
        return Cache::remember('dashboard_units_comparison', self::COMPARISON_TTL, function () {
            $units = GenerationUnit::with(['operator', 'generators', 'governorateDetail', 'cityDetail'])->get();

            if ($units->isEmpty()) return [];

            $allGeneratorIds = $units->flatMap(fn($u) => $u->generators->pluck('id'));

            // Aggregate operation logs per generator
            $opLogs = OperationLog::whereIn('generator_id', $allGeneratorIds)
                ->select(
                    'generator_id',
                    DB::raw('SUM(energy_produced) as total_energy'),
                    DB::raw('SUM(fuel_consumed) as total_fuel'),
                    DB::raw('COUNT(*) as operations_count'),
                    DB::raw('AVG(load_percentage) as avg_load'),
                    DB::raw('SUM(CASE WHEN MONTH(operation_date) = ' . now()->month . ' AND YEAR(operation_date) = ' . now()->year . ' THEN energy_produced ELSE 0 END) as this_month_energy')
                )
                ->groupBy('generator_id')
                ->get()
                ->keyBy('generator_id');

            return $units->map(function ($unit) use ($opLogs) {
                $generatorIds = $unit->generators->pluck('id');

                // Sum from pre-fetched aggregates
                $totalEnergy = $generatorIds->sum(fn($id) => (float) ($opLogs[$id]->total_energy ?? 0));
                $totalFuel = $generatorIds->sum(fn($id) => (float) ($opLogs[$id]->total_fuel ?? 0));
                $operationsCount = $generatorIds->sum(fn($id) => (int) ($opLogs[$id]->operations_count ?? 0));
                $thisMonthEnergy = $generatorIds->sum(fn($id) => (float) ($opLogs[$id]->this_month_energy ?? 0));

                $loadsWithData = $generatorIds->filter(fn($id) => isset($opLogs[$id]));
                $avgLoad = $loadsWithData->count() > 0
                    ? $loadsWithData->avg(fn($id) => (float) ($opLogs[$id]->avg_load ?? 0))
                    : 0;

                $efficiency = $totalFuel > 0 ? round($totalEnergy / $totalFuel, 2) : 0;
                $installedCapacity = $unit->generators->sum('capacity_kva') ?? 0;

                $avgIdealEfficiency = $unit->generators->avg('ideal_fuel_efficiency') ?? 0.5;
                $expectedEnergy = $avgIdealEfficiency > 0 ? $totalFuel * $avgIdealEfficiency : 0;
                $energyLoss = max(0, $expectedEnergy - $totalEnergy);
                $lossPercentage = $expectedEnergy > 0 ? round(($energyLoss / $expectedEnergy) * 100, 2) : 0;

                return [
                    'id' => $unit->id,
                    'name' => $unit->name ?? $unit->unit_code,
                    'unit_code' => $unit->unit_code,
                    'operator_id' => $unit->operator_id,
                    'operator_name' => $unit->operator?->name,
                    'governorate' => $unit->governorateDetail?->label,
                    'city' => $unit->cityDetail?->label,
                    'generators_count' => $unit->generators->count(),
                    'installed_capacity' => round($installedCapacity, 2),
                    'unit_capacity' => (int) ($unit->total_capacity ?? 0),
                    'beneficiaries_count' => $unit->beneficiaries_count ?? 0,
                    'total_energy' => round($totalEnergy, 2),
                    'total_fuel' => round($totalFuel, 2),
                    'this_month_energy' => round($thisMonthEnergy, 2),
                    'efficiency' => $efficiency,
                    'avg_load' => round($avgLoad, 2),
                    'operations_count' => $operationsCount,
                    'energy_loss' => round($energyLoss, 2),
                    'loss_percentage' => $lossPercentage,
                ];
            })
                ->sortByDesc('total_energy')
                ->values()
                ->toArray();
        });
    }

    /**
     * آخر العناصر (recent items)
     */
    public function getRecentItems($user, ?array $operatorIds, ?array $generatorIds): array
    {
        $recentGenerators = Generator::with(['generationUnit.operator'])
            ->when($user->isCompanyOwner(), function ($query) use ($user) {
                $opIds = $user->ownedOperators->pluck('id');
                $unitIds = GenerationUnit::whereIn('operator_id', $opIds)->pluck('id');
                $query->whereIn('generation_unit_id', $unitIds);
            })
            ->when($user->isAffiliatedWithOperator() || $user->isCivilDefense(), function ($query) use ($user) {
                // يشمل: Employee, Technician, CivilDefense, وأي دور مخصص (custom role) مرتبط بمشغل
                $opIds = $user->getScopedOperatorIds();
                $unitIds = GenerationUnit::whereIn('operator_id', $opIds)->pluck('id');
                $query->whereIn('generation_unit_id', $unitIds);
            })
            ->latest()
            ->limit(5)
            ->get();

        $recentOperators = ($user->isSuperAdmin() || $user->isAdmin())
            ? Operator::with('owner')->latest()->limit(5)->get()
            : collect();

        $recentOperationLogs = OperationLog::with(['generator', 'operator'])
            ->when($operatorIds, fn($q) => $q->whereIn('operator_id', $operatorIds))
            ->when($generatorIds, fn($q) => $q->whereIn('generator_id', $generatorIds))
            ->latest('operation_date')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $generatorsNeedingMaintenance = Generator::with('generationUnit.operator')
            ->when(!$user->isSuperAdmin() && !$user->isAdmin(), function ($q) use ($operatorIds) {
                $unitIds = GenerationUnit::whereIn('operator_id', $operatorIds ?? [])->pluck('id');
                $q->whereIn('generation_unit_id', $unitIds);
            })
            ->where(function ($query) {
                $query->whereNull('last_major_maintenance_date')
                    ->orWhere('last_major_maintenance_date', '<', Carbon::now()->subMonths(6));
            })
            ->limit(5)
            ->get();

        $expiringCompliance = $this->getExpiringCompliance($user, $operatorIds);

        return compact(
            'recentGenerators',
            'recentOperators',
            'recentOperationLogs',
            'generatorsNeedingMaintenance',
            'expiringCompliance'
        );
    }

    /**
     * الشهادات المنتهية أو قريبة من الانتهاء
     */
    public function getExpiringCompliance($user, ?array $operatorIds)
    {
        $query = ComplianceSafety::with(['operator', 'safetyCertificateStatusDetail']);

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            $query->whereIn('operator_id', $operatorIds ?? []);
        }

        $expiredStatusId = ConstantDetail::whereHas('master', fn($q) => $q->where('constant_number', 13))
            ->where('code', 'EXPIRED')->value('id');

        $validStatusId = ConstantDetail::whereHas('master', fn($q) => $q->where('constant_number', 13))
            ->where('code', 'VALID')->value('id');

        return $query->where(function ($q) use ($expiredStatusId, $validStatusId) {
            if ($expiredStatusId) {
                $q->where('safety_certificate_status_id', $expiredStatusId);
            }
            if ($validStatusId) {
                $q->orWhere(function ($subQuery) use ($validStatusId) {
                    $subQuery->where('safety_certificate_status_id', $validStatusId)
                        ->where(function ($dateQuery) {
                            $dateQuery->whereNull('last_inspection_date')
                                ->orWhere('last_inspection_date', '<', Carbon::now()->subMonths(6));
                        });
                });
            }
        })
            ->when($expiredStatusId, fn($q) => $q->orderByRaw("CASE WHEN safety_certificate_status_id = {$expiredStatusId} THEN 0 ELSE 1 END"))
            ->latest('last_inspection_date')
            ->limit(5)
            ->get();
    }

    /**
     * بيانات المهام للفني والدفاع المدني
     */
    public function getTasksData($user): ?array
    {
        if (!$user->isTechnician() && !$user->isCivilDefense()) {
            return null;
        }

        $tasks = Task::where('assigned_to', $user->id)
            ->with(['operator', 'generationUnit', 'generator', 'assignedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $stats = [
            'total' => Task::where('assigned_to', $user->id)->count(),
            'pending' => Task::where('assigned_to', $user->id)->where('status', 'pending')->count(),
            'in_progress' => Task::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'completed' => Task::where('assigned_to', $user->id)->where('status', 'completed')->count(),
            'overdue' => Task::where('assigned_to', $user->id)
                ->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return compact('tasks', 'stats');
    }

    /**
     * إنشاء الإشعارات — مع cache guard
     */
    public function createNotificationsIfNeeded($user, $generatorsNeedingMaintenance, $expiringCompliance): void
    {
        $cacheKey = "dashboard_notifications_{$user->id}";
        if (Cache::has($cacheKey)) return;

        if ($generatorsNeedingMaintenance->count() > 0) {
            $count = $generatorsNeedingMaintenance->count();
            $firstGeneratorId = $generatorsNeedingMaintenance->first()->id;
            $this->createOrUpdateNotification(
                $user->id,
                'maintenance_needed',
                'مولدات تحتاج صيانة',
                "يوجد {$count} مولد يحتاج إلى صيانة فورية",
                route('admin.maintenance-records.create', ['generator_id' => $firstGeneratorId])
            );
        }

        if ($expiringCompliance->count() > 0) {
            $count = $expiringCompliance->count();
            $this->createOrUpdateNotification(
                $user->id,
                'compliance_expiring',
                'شهادات منتهية أو قريبة من الانتهاء',
                "يوجد {$count} شهادة تحتاج إلى متابعة",
                route('admin.compliance-safeties.index')
            );
        }

        Cache::put($cacheKey, true, self::STATS_TTL);
    }

    private function createOrUpdateNotification(int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        $existing = Notification::where('user_id', $userId)
            ->where('type', $type)
            ->where('read', false)
            ->first();

        if ($existing) {
            $existing->update([
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'created_at' => now(),
            ]);
        } else {
            Notification::createNotification($userId, $type, $title, $message, $link);
        }
    }

    // ╔══════════════════════════════════════════════════════════════════════╗
    // ║                 BILLING & COLLECTION DASHBOARD                        ║
    // ║                                                                       ║
    // ║ Public API:                                                           ║
    // ║   getBillingDashboard($operatorIds): nullable array (null = no scope) ║
    // ║                                                                       ║
    // ║ Scoping rules — guaranteed at the SQL layer in every helper:          ║
    // ║   - $operatorIds === null  → user has global accounting access; no    ║
    // ║                              operator filter applied                  ║
    // ║   - $operatorIds === []    → user is scoped to nothing (e.g. a        ║
    // ║                              technician without any operator); we     ║
    // ║                              short-circuit and return null            ║
    // ║   - $operatorIds = [a, b]  → restricted via whereIn on the operator   ║
    // ║                              join                                     ║
    // ║                                                                       ║
    // ║ All paid amounts are FIFO-allocated per subscriber, mirroring the     ║
    // ║ invoice-reports module so the dashboard and reports never disagree.   ║
    // ╚══════════════════════════════════════════════════════════════════════╝

    /**
     * Top-level entry point: build the entire billing widget payload.
     * Returns null when the user has no scope (so the view can hide the section).
     */
    public function getBillingDashboard(?array $operatorIds): ?array
    {
        // Restricted user with no operator link → no data to show
        if (is_array($operatorIds) && empty($operatorIds)) {
            return null;
        }

        $hash = md5(json_encode($operatorIds));
        return Cache::remember("dashboard_billing_{$hash}", self::CHARTS_TTL, function () use ($operatorIds) {
            $paidByInvoice = $this->billingComputeFifoPaid($operatorIds);

            return [
                'current_month'   => $this->billingCurrentMonth($operatorIds, $paidByInvoice),
                'trend'           => $this->billingTrend($operatorIds, $paidByInvoice, 6),
                'aging'           => $this->billingAging($operatorIds, $paidByInvoice),
                'top_debtors'     => $this->billingTopDebtors($operatorIds, $paidByInvoice, 5),
                'by_operator'     => $this->billingByOperator($operatorIds, $paidByInvoice),
            ];
        });
    }

    /**
     * Apply the operator scope to an Invoice query (or any builder that joins
     * subscriber.generationUnits). Centralises the SQL gate so no caller can
     * accidentally forget to scope.
     */
    private function billingScopeInvoices($query, ?array $operatorIds)
    {
        if ($operatorIds === null) {
            return $query; // global access
        }
        return $query->whereHas('subscriber.generationUnits', function ($q) use ($operatorIds) {
            $q->whereIn('operator_id', $operatorIds);
        });
    }

    /**
     * FIFO-allocated paid amount per invoice for the user's scope.
     * Same algorithm as InvoiceReportController::computeFifoPaidByInvoice.
     * Returns map [invoice_id => allocated_amount].
     */
    private function billingComputeFifoPaid(?array $operatorIds): array
    {
        // Find subscribers in scope (respects operator boundaries)
        $subscriberQuery = \App\Models\Subscriber::query();
        if ($operatorIds !== null) {
            $subscriberQuery->whereHas('generationUnits', fn($q) => $q->whereIn('operator_id', $operatorIds));
        }
        $subscriberIds = $subscriberQuery->pluck('id');
        if ($subscriberIds->isEmpty()) {
            return [];
        }

        $allInvoices = \App\Models\Invoice::whereIn('subscriber_id', $subscriberIds)
            ->whereNotIn('invoice_status', [\App\Models\Invoice::STATUS_DRAFT, \App\Models\Invoice::STATUS_CANCELLED])
            ->orderBy('subscriber_id')->orderBy('invoice_date')->orderBy('id')
            ->get(['id', 'subscriber_id', 'invoice_amount']);

        $invoiceToSub = $allInvoices->pluck('subscriber_id', 'id');
        $pools = [];
        \App\Models\Payment::whereIn('invoice_id', $allInvoices->pluck('id'))
            ->select('invoice_id', 'amount_paid')
            ->get()
            ->each(function ($p) use ($invoiceToSub, &$pools) {
                $subId = $invoiceToSub[$p->invoice_id] ?? null;
                if ($subId !== null) {
                    $pools[$subId] = ($pools[$subId] ?? 0) + (float) $p->amount_paid;
                }
            });

        $paid = [];
        foreach ($allInvoices->groupBy('subscriber_id') as $subId => $invoices) {
            $pool = (float) ($pools[$subId] ?? 0);
            foreach ($invoices as $inv) {
                $charge = (float) $inv->invoice_amount;
                $alloc  = $charge > 0 ? min($pool, $charge) : 0;
                $pool  -= $alloc;
                $paid[$inv->id] = $alloc;
            }
        }
        return $paid;
    }

    /**
     * KPIs for the current month (1st of month through today).
     */
    private function billingCurrentMonth(?array $operatorIds, array $paidByInvoice): array
    {
        $from = Carbon::now()->startOfMonth();
        $to   = Carbon::now();

        $invoices = $this->billingScopeInvoices(
            \App\Models\Invoice::whereIn('invoice_status', [
                \App\Models\Invoice::STATUS_ISSUED,
                \App\Models\Invoice::STATUS_PARTIAL,
                \App\Models\Invoice::STATUS_PAID,
            ])->whereBetween('invoice_date', [$from, $to]),
            $operatorIds
        )->get(['id', 'invoice_amount', 'subscriber_id']);

        $billed     = (float) $invoices->sum('invoice_amount');
        $collected  = (float) $invoices->sum(fn($i) => $paidByInvoice[$i->id] ?? 0);
        $remaining  = max($billed - $collected, 0);
        $rate       = $billed > 0 ? round(($collected / $billed) * 100, 1) : 0;

        // Overdue right now (anywhere — not just this month, because debt accumulates)
        $overdueAgg = $this->billingOverdueAggregate($operatorIds, $paidByInvoice);

        return [
            'invoice_count'      => $invoices->count(),
            'subscriber_count'   => $invoices->pluck('subscriber_id')->unique()->count(),
            'billed'             => round($billed, 2),
            'collected'          => round($collected, 2),
            'remaining'          => round($remaining, 2),
            'collection_rate'    => $rate,
            'overdue_count'      => $overdueAgg['count'],
            'overdue_amount'     => $overdueAgg['amount'],
        ];
    }

    /**
     * Total overdue invoices/amount within scope (across all time, not just current month).
     */
    private function billingOverdueAggregate(?array $operatorIds, array $paidByInvoice): array
    {
        $invoices = $this->billingScopeInvoices(
            \App\Models\Invoice::whereIn('invoice_status', [
                \App\Models\Invoice::STATUS_ISSUED,
                \App\Models\Invoice::STATUS_PARTIAL,
            ])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()),
            $operatorIds
        )->get(['id', 'invoice_amount']);

        $count  = 0;
        $amount = 0.0;
        foreach ($invoices as $inv) {
            $r = max((float) $inv->invoice_amount - (float) ($paidByInvoice[$inv->id] ?? 0), 0);
            if ($r > 0) {
                $count++;
                $amount += $r;
            }
        }
        return ['count' => $count, 'amount' => round($amount, 2)];
    }

    /**
     * Trend chart data: last N months of billing + collection (FIFO-aware).
     */
    private function billingTrend(?array $operatorIds, array $paidByInvoice, int $months = 6): array
    {
        $labels = $billed = $collected = $rates = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $labels[] = $start->translatedFormat('M Y');

            $invoices = $this->billingScopeInvoices(
                \App\Models\Invoice::whereIn('invoice_status', [
                    \App\Models\Invoice::STATUS_ISSUED,
                    \App\Models\Invoice::STATUS_PARTIAL,
                    \App\Models\Invoice::STATUS_PAID,
                ])->whereBetween('invoice_date', [$start, $end]),
                $operatorIds
            )->get(['id', 'invoice_amount']);

            $b = (float) $invoices->sum('invoice_amount');
            $c = (float) $invoices->sum(fn($i) => $paidByInvoice[$i->id] ?? 0);

            $billed[]    = round($b, 2);
            $collected[] = round($c, 2);
            $rates[]     = $b > 0 ? round(($c / $b) * 100, 1) : 0;
        }
        return compact('labels', 'billed', 'collected', 'rates');
    }

    /**
     * Aging buckets for overdue receivables.
     */
    private function billingAging(?array $operatorIds, array $paidByInvoice): array
    {
        $invoices = $this->billingScopeInvoices(
            \App\Models\Invoice::whereIn('invoice_status', [
                \App\Models\Invoice::STATUS_ISSUED,
                \App\Models\Invoice::STATUS_PARTIAL,
            ])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()),
            $operatorIds
        )->get(['id', 'invoice_amount', 'due_date']);

        $buckets = [
            '0_30'  => ['label' => '0-30 يوم',     'count' => 0, 'amount' => 0.0],
            '31_60' => ['label' => '31-60 يوم',    'count' => 0, 'amount' => 0.0],
            '61_90' => ['label' => '61-90 يوم',    'count' => 0, 'amount' => 0.0],
            '90p'   => ['label' => 'أكثر من 90 يوم', 'count' => 0, 'amount' => 0.0],
        ];

        foreach ($invoices as $inv) {
            $remaining = max((float) $inv->invoice_amount - (float) ($paidByInvoice[$inv->id] ?? 0), 0);
            if ($remaining <= 0) continue;
            $days = (int) now()->diffInDays($inv->due_date);
            $key = match (true) {
                $days <= 30  => '0_30',
                $days <= 60  => '31_60',
                $days <= 90  => '61_90',
                default      => '90p',
            };
            $buckets[$key]['count']++;
            $buckets[$key]['amount'] += $remaining;
        }
        foreach ($buckets as &$b) {
            $b['amount'] = round($b['amount'], 2);
        }
        return array_values($buckets);
    }

    /**
     * Top N subscribers with the largest unpaid balance — for CompanyOwner view.
     */
    private function billingTopDebtors(?array $operatorIds, array $paidByInvoice, int $limit = 5): array
    {
        $invoices = $this->billingScopeInvoices(
            \App\Models\Invoice::whereIn('invoice_status', [
                \App\Models\Invoice::STATUS_ISSUED,
                \App\Models\Invoice::STATUS_PARTIAL,
            ])->with(['subscriber:id,subscriber_name,subscription_number']),
            $operatorIds
        )->get();

        $bySub = [];
        foreach ($invoices as $inv) {
            $r = max((float) $inv->invoice_amount - (float) ($paidByInvoice[$inv->id] ?? 0), 0);
            if ($r <= 0 || ! $inv->subscriber) continue;
            $key = $inv->subscriber_id;
            if (! isset($bySub[$key])) {
                $bySub[$key] = [
                    'subscriber_name'     => $inv->subscriber->subscriber_name,
                    'subscription_number' => $inv->subscriber->subscription_number,
                    'remaining'           => 0.0,
                    'invoice_count'       => 0,
                ];
            }
            $bySub[$key]['remaining']    += $r;
            $bySub[$key]['invoice_count']++;
        }
        foreach ($bySub as &$s) {
            $s['remaining'] = round($s['remaining'], 2);
        }
        usort($bySub, fn($a, $b) => $b['remaining'] <=> $a['remaining']);
        return array_slice(array_values($bySub), 0, $limit);
    }

    /**
     * Per-operator billing summary — for SuperAdmin/EnergyAuthority/GeneralAccountant.
     * Returns rows sorted by lowest collection rate first (worst performers up top).
     */
    private function billingByOperator(?array $operatorIds, array $paidByInvoice): array
    {
        // Limit the operator list to those in scope (or all for global access)
        $opQuery = Operator::query()->select('id', 'name')->where('status', 'active');
        if ($operatorIds !== null) {
            $opQuery->whereIn('id', $operatorIds);
        }
        $operators = $opQuery->get();
        if ($operators->isEmpty()) {
            return [];
        }

        // Fetch all non-draft non-cancelled invoices for these operators (just what we need)
        $invoices = \App\Models\Invoice::whereNotIn('invoice_status', [
                \App\Models\Invoice::STATUS_DRAFT,
                \App\Models\Invoice::STATUS_CANCELLED,
            ])
            ->whereHas('subscriber.generationUnits', fn($q) => $q->whereIn('operator_id', $operators->pluck('id')))
            ->with(['subscriber.generationUnits:id,operator_id'])
            ->get(['id', 'subscriber_id', 'invoice_amount']);

        // Build per-operator totals
        $totals = [];
        foreach ($operators as $op) {
            $totals[$op->id] = [
                'id'              => $op->id,
                'name'            => $op->name,
                'billed'          => 0.0,
                'collected'       => 0.0,
                'invoice_count'   => 0,
            ];
        }

        foreach ($invoices as $inv) {
            $opId = $inv->subscriber?->generationUnits?->first()?->operator_id;
            if (! $opId || ! isset($totals[$opId])) continue;

            $totals[$opId]['billed']        += (float) $inv->invoice_amount;
            $totals[$opId]['collected']     += (float) ($paidByInvoice[$inv->id] ?? 0);
            $totals[$opId]['invoice_count']++;
        }

        foreach ($totals as &$t) {
            $t['billed']          = round($t['billed'], 2);
            $t['collected']       = round($t['collected'], 2);
            $t['remaining']       = round(max($t['billed'] - $t['collected'], 0), 2);
            $t['collection_rate'] = $t['billed'] > 0 ? round(($t['collected'] / $t['billed']) * 100, 1) : 0;
        }
        unset($t);

        $totals = array_values($totals);
        // Worst performers (active billing but low rate) first; then operators
        // that haven't started billing at all
        usort($totals, function ($a, $b) {
            if ($a['invoice_count'] === 0 && $b['invoice_count'] === 0) return strcmp($a['name'], $b['name']);
            if ($a['invoice_count'] === 0) return 1;   // empty operators last
            if ($b['invoice_count'] === 0) return -1;
            return $a['collection_rate'] <=> $b['collection_rate'];
        });

        return $totals;
    }
}
