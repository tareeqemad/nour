<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceSafety;
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
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // التحقق من اكتمال بيانات المشغل
        if ($user->isCompanyOwner()) {
            $operator = $user->ownedOperators()->first();
            if ($operator) {
                // تحديث profile_completed بناءً على البيانات الفعلية (لتجنب مشاكل الـ cache)
                // يجب إدخال الحقول الأربعة: name, owner_name, owner_id_number, operator_id_number
                $isComplete = !empty($operator->name) 
                    && !empty($operator->owner_name) 
                    && !empty($operator->owner_id_number) 
                    && !empty($operator->operator_id_number);
                if ($operator->profile_completed != $isComplete) {
                    $operator->update(['profile_completed' => $isComplete]);
                    $operator->refresh();
                }
                
                // التحقق مرة أخرى بعد التحديث
                if (! $operator->isProfileComplete()) {
                    return redirect()->route('admin.operators.profile')
                        ->with('warning', 'يرجى إكمال بيانات المشغل أولاً.');
                }
            }
        }

        // تحديد النطاق حسب نوع المستخدم
        $operatorIds = $this->getOperatorIds($user);
        $generatorIds = $this->getGeneratorIds($user, $operatorIds);

        // إحصائيات عامة
        if ($user->isSuperAdmin()) {
            $stats = $this->getSuperAdminStats();
        } elseif ($user->isAdmin()) {
            $stats = $this->getAdminStats();
        } elseif ($user->isCompanyOwner()) {
            $stats = $this->getCompanyOwnerStats($user, $operatorIds, $generatorIds);
        } elseif ($user->isEmployee() || $user->isTechnician()) {
            $stats = $this->getEmployeeStats($user, $operatorIds, $generatorIds);
        } elseif ($user->isCivilDefense()) {
            $stats = $this->getCivilDefenseStats($user);
        } else {
            $stats = [];
        }

        // إحصائيات سجلات التشغيل
        $operationStats = $this->getOperationStats($operatorIds, $generatorIds);

        // بيانات المخططات (آخر 30 يوم)
        $chartData = $this->getChartData($operatorIds, $generatorIds);

        // بيانات المخطط الدائري (المولدات/المشغلين والطاقة المنتجة)
        $pieChartData = $this->getPieChartData($operatorIds, $generatorIds);

        // إحصائيات الصيانة
        $maintenanceStats = $this->getMaintenanceStats($generatorIds);

        // إحصائيات كفاءة الوقود
        $fuelStats = $this->getFuelStats($generatorIds);

        // إحصائيات الامتثال والسلامة
        $complianceStats = $this->getComplianceStats($operatorIds);

        // آخر المولدات المضافة
        $recentGenerators = Generator::with(['generationUnit.operator'])
            ->when($user->isCompanyOwner(), function ($query) use ($user) {
                $operatorIds = $user->ownedOperators->pluck('id');
                $generationUnitIds = GenerationUnit::whereIn('operator_id', $operatorIds)->pluck('id');
                $query->whereIn('generation_unit_id', $generationUnitIds);
            })
            ->when($user->isEmployee() || $user->isTechnician() || $user->isCivilDefense(), function ($query) use ($user) {
                $operatorIds = $user->operators->pluck('id');
                $generationUnitIds = GenerationUnit::whereIn('operator_id', $operatorIds)->pluck('id');
                $query->whereIn('generation_unit_id', $generationUnitIds);
            })
            ->latest()
            ->limit(5)
            ->get();

        // آخر المشغلين المضافة (Super Admin والأدمن)
        $recentOperators = ($user->isSuperAdmin() || $user->isAdmin())
            ? Operator::with('owner')->latest()->limit(5)->get()
            : collect();

        // آخر سجلات التشغيل (بما في ذلك المولدات المحذوفة soft delete)
        $recentOperationLogs = OperationLog::with(['generator', 'operator'])
            ->when($operatorIds, fn($q) => $q->whereIn('operator_id', $operatorIds))
            ->when($generatorIds, fn($q) => $q->whereIn('generator_id', $generatorIds))
            ->latest('operation_date')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // المولدات التي تحتاج صيانة
        $generatorsNeedingMaintenance = Generator::with('generationUnit.operator')
            ->when($operatorIds, function($q) use ($operatorIds) {
                $generationUnitIds = GenerationUnit::whereIn('operator_id', $operatorIds)->pluck('id');
                $q->whereIn('generation_unit_id', $generationUnitIds);
            })
            ->where(function ($query) {
                $query->whereNull('last_major_maintenance_date')
                    ->orWhere('last_major_maintenance_date', '<', Carbon::now()->subMonths(6));
            })
            ->limit(5)
            ->get();


        // الشهادات المنتهية أو قريبة من الانتهاء
        $expiringCompliance = $this->getExpiringCompliance($operatorIds);

        // بيانات المهام (للفني والدفاع المدني)
        $tasksData = null;
        if ($user->isTechnician() || $user->isCivilDefense()) {
            $tasksData = $this->getTasksData($user);
        }

        $this->createNotifications($user, $operatorIds, $generatorIds, $generatorsNeedingMaintenance, $expiringCompliance);

        return view('admin.dashboard', compact(
            'stats',
            'operationStats',
            'chartData',
            'pieChartData',
            'maintenanceStats',
            'fuelStats',
            'complianceStats',
            'recentGenerators',
            'recentOperators',
            'recentOperationLogs',
            'generatorsNeedingMaintenance',
            'expiringCompliance',
            'tasksData'
        ));
    }

    /**
     * تحديد نطاق المشغلين حسب نوع المستخدم
     * 
     * للسوبر أدمن والأدمن: يرجع null = جميع المشغلين
     * للمشغلين: يرجع المشغلين المملوكين
     * للموظفين: يرجع المشغلين المرتبطين
     */
    private function getOperatorIds($user): ?array
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return null; // جميع المشغلين (لا فلترة)
        } elseif ($user->isCompanyOwner()) {
            return $user->ownedOperators->pluck('id')->toArray();
        } elseif ($user->isEmployee() || $user->isTechnician() || $user->isCivilDefense()) {
            return $user->operators->pluck('id')->toArray();
        }
        return [];
    }

    /**
     * تحديد نطاق المولدات حسب نوع المستخدم
     * 
     * للسوبر أدمن والأدمن: يرجع null = جميع المولدات
     * للمشغلين والموظفين: يرجع المولدات التابعة للمشغلين المحددين
     */
    private function getGeneratorIds($user, ?array $operatorIds): ?array
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return null; // جميع المولدات (لا فلترة)
        }

        if ($operatorIds) {
            // الحصول على وحدات التوليد المرتبطة بالمشغلين
            $generationUnitIds = GenerationUnit::whereIn('operator_id', $operatorIds)->pluck('id');
            
            // الحصول على المولدات المرتبطة بوحدات التوليد
            return Generator::whereIn('generation_unit_id', $generationUnitIds)->pluck('id')->toArray();
        }

        return [];
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
                'active' => Generator::whereHas('statusDetail', function($q) {
                    $q->where('code', 'ACTIVE');
                })->count(),
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
                'active' => Generator::whereHas('statusDetail', function($q) {
                    $q->where('code', 'ACTIVE');
                })->count(),
            ],
            'company_owners' => [
                'total' => User::where('role', Role::CompanyOwner)->count(),
            ],
        ];
    }

    private function getCompanyOwnerStats($user, ?array $operatorIds, ?array $generatorIds): array
    {
        $ownedOperators = $user->ownedOperators;
        
        return [
            'operators' => [
                'total' => $ownedOperators->count(),
                'active' => $ownedOperators->where('status', 'active')->count(),
            ],
            'generators' => [
                'total' => Generator::whereHas('generationUnit', function($q) use ($ownedOperators) {
                    $q->whereIn('operator_id', $ownedOperators->pluck('id'));
                })->count(),
                'active' => Generator::whereHas('generationUnit', function($q) use ($ownedOperators) {
                    $q->whereIn('operator_id', $ownedOperators->pluck('id'));
                })->whereHas('statusDetail', function($q) {
                    $q->where('code', 'ACTIVE');
                })->count(),
            ],
            'employees' => [
                'total' => User::whereHas('operators', function ($query) use ($ownedOperators) {
                    $query->whereIn('operators.id', $ownedOperators->pluck('id'));
                })->where('role', Role::Employee)->distinct()->count(),
            ],
        ];
    }

    private function getEmployeeStats($user, ?array $operatorIds, ?array $generatorIds): array
    {
        $userOperators = $user->operators;
        
        return [
            'operators' => [
                'total' => $userOperators->count(),
            ],
            'generators' => [
                'total' => Generator::whereHas('generationUnit', function($q) use ($userOperators) {
                    $q->whereIn('operator_id', $userOperators->pluck('id'));
                })->count(),
                'active' => Generator::whereHas('generationUnit', function($q) use ($userOperators) {
                    $q->whereIn('operator_id', $userOperators->pluck('id'));
                })->whereHas('statusDetail', function($q) {
                    $q->where('code', 'ACTIVE');
                })->count(),
            ],
        ];
    }

    private function getOperationStats(?array $operatorIds, ?array $generatorIds): array
    {
        $baseQuery = function() use ($operatorIds, $generatorIds) {
            $query = OperationLog::query();
            if ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            }
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        $totalLogs = $baseQuery()->count();
        $thisMonth = (clone $baseQuery())->whereMonth('operation_date', Carbon::now()->month)
            ->whereYear('operation_date', Carbon::now()->year)->count();
        $thisWeek = (clone $baseQuery())->whereBetween('operation_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        $totalEnergy = (clone $baseQuery())->sum('energy_produced') ?? 0;
        $totalFuel = (clone $baseQuery())->sum('fuel_consumed') ?? 0;
        $avgLoad = (clone $baseQuery())->avg('load_percentage') ?? 0;

        return [
            'total' => $totalLogs,
            'this_month' => $thisMonth,
            'this_week' => $thisWeek,
            'total_energy' => round($totalEnergy, 2),
            'total_fuel' => round($totalFuel, 2),
            'avg_load' => round($avgLoad, 2),
        ];
    }

    /**
     * الحصول على بيانات المخططات (آخر 30 يوم)
     * 
     * للسوبر أدمن والأدمن: $operatorIds و $generatorIds يكونان null
     * مما يعني جلب الإجمالي لجميع المشغلين والمولدات
     * 
     * للمشغلين والموظفين: يتم فلترة البيانات حسب المشغلين/المولدات المحددة
     */
    private function getChartData(?array $operatorIds, ?array $generatorIds): array
    {
        $baseQuery = function() use ($operatorIds, $generatorIds) {
            $query = OperationLog::query();
            // إذا كان $operatorIds null (سوبر أدمن/أدمن)، لا يتم تطبيق فلتر = جميع المشغلين
            if ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            }
            // إذا كان $generatorIds null (سوبر أدمن/أدمن)، لا يتم تطبيق فلتر = جميع المولدات
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        // الحصول على بيانات آخر 30 يوم
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        // تجميع البيانات حسب التاريخ (SUM لكل المشغلين/المولدات)
        $query = (clone $baseQuery())
            ->whereBetween('operation_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(operation_date) as date'),
                DB::raw('SUM(energy_produced) as total_energy'), // الإجمالي لكل المشغلين
                DB::raw('SUM(fuel_consumed) as total_fuel'), // الإجمالي لكل المشغلين
                DB::raw('COUNT(*) as records_count'),
                DB::raw('AVG(load_percentage) as avg_load')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // إنشاء مصفوفة بجميع الأيام (آخر 30 يوم)
        $dates = [];
        $energyData = [];
        $fuelData = [];
        $recordsData = [];
        $loadData = [];

        // تحويل نتائج الاستعلام إلى مصفوفة مفهرسة بالتاريخ
        $dataByDate = [];
        foreach ($query as $row) {
            $dataByDate[$row->date] = $row;
        }

        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = Carbon::now()->subDays($i)->format('d/m');
            
            $dayData = $dataByDate[$date] ?? null;
            
            $energyData[] = $dayData ? round((float)$dayData->total_energy, 2) : 0;
            $fuelData[] = $dayData ? round((float)$dayData->total_fuel, 2) : 0;
            $recordsData[] = $dayData ? (int)$dayData->records_count : 0;
            $loadData[] = $dayData ? round((float)$dayData->avg_load, 2) : 0;
        }

        // حساب الفاقد في الوقود (Fuel Loss/Waste)
        // الفاقد = الوقود المستهلك - (الطاقة المنتجة / كفاءة الوقود المثالية)
        // استخدام كفاءة الوقود المثالية من المولدات (أو القيمة الافتراضية 0.5)
        $fuelLossData = [];
        
        // الحصول على متوسط كفاءة الوقود المثالية من المولدات المستخدمة
        $generatorsQuery = Generator::query();
        if ($generatorIds) {
            $generatorsQuery->whereIn('id', $generatorIds);
        } elseif ($operatorIds) {
            $generatorsQuery->whereIn('operator_id', $operatorIds);
        }
        
        $avgIdealEfficiency = $generatorsQuery->avg('ideal_fuel_efficiency') ?? 0.5;
        // إذا لم تكن هناك قيمة محددة، نستخدم القيمة الافتراضية
        if ($avgIdealEfficiency <= 0) {
            $avgIdealEfficiency = 0.5;
        }
        
        foreach ($energyData as $index => $energy) {
            $fuelConsumed = $fuelData[$index];
            if ($energy > 0 && $avgIdealEfficiency > 0) {
                $expectedFuel = $energy / $avgIdealEfficiency;
                $fuelLoss = max(0, $fuelConsumed - $expectedFuel); // الفاقد لا يمكن أن يكون سالب
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
    }

    private function getPieChartData(?array $operatorIds, ?array $generatorIds): array
    {
        $baseQuery = function() use ($operatorIds, $generatorIds) {
            $query = OperationLog::query();
            if ($operatorIds) {
                $query->whereIn('operation_logs.operator_id', $operatorIds);
            }
            if ($generatorIds) {
                $query->whereIn('operation_logs.generator_id', $generatorIds);
            }
            return $query;
        };

        // بيانات المولدات مع الطاقة المنتجة والوقود المستهلك
        $generatorsData = (clone $baseQuery())
            ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
            ->select(
                'generators.id',
                'generators.name',
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
            ->map(function($item) {
                $consumed = (float)$item->total_fuel_consumed;
                $energy = (float)$item->total_energy;
                
                // حساب كفاءة الوقود (kWh/لتر)
                $fuel_efficiency = $consumed > 0 ? round($energy / $consumed, 2) : 0;
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'energy' => round($energy, 2),
                    'fuel_consumed' => round($consumed, 2),
                    'fuel_efficiency' => $fuel_efficiency, // كفاءة الوقود (kWh/لتر)
                    'avg_load' => round((float)$item->avg_load, 2), // متوسط نسبة التحميل
                    'records_count' => (int)$item->records_count, // عدد السجلات
                ];
            });

        // بيانات المشغلين مع الطاقة المنتجة والوقود المستهلك
        $operatorsData = (clone $baseQuery())
            ->join('operators', 'operation_logs.operator_id', '=', 'operators.id')
            ->join('generators', 'operation_logs.generator_id', '=', 'generators.id')
            ->select(
                'operators.id',
                'operators.name',
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
            ->map(function($item) {
                $consumed = (float)$item->total_fuel_consumed;
                $energy = (float)$item->total_energy;
                
                // حساب كفاءة الوقود (kWh/لتر)
                $fuel_efficiency = $consumed > 0 ? round($energy / $consumed, 2) : 0;
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'energy' => round($energy, 2),
                    'fuel_consumed' => round($consumed, 2),
                    'fuel_efficiency' => $fuel_efficiency, // كفاءة الوقود (kWh/لتر)
                    'avg_load' => round((float)$item->avg_load, 2), // متوسط نسبة التحميل
                    'records_count' => (int)$item->records_count, // عدد السجلات
                ];
            });

        // بيانات المحافظات مع الطاقة المنتجة والوقود المستهلك
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
            ->map(function($item) {
                $governorateValue = (int)$item->governorate_value;
                $governorate = \App\Governorate::tryFrom($governorateValue);
                $consumed = (float)$item->total_fuel_consumed;
                $energy = (float)$item->total_energy;
                
                // حساب كفاءة الوقود (kWh/لتر)
                $fuel_efficiency = $consumed > 0 ? round($energy / $consumed, 2) : 0;
                
                return [
                    'id' => $governorateValue,
                    'name' => $governorate ? $governorate->label() : 'غير محدد',
                    'code' => $governorate ? $governorate->code() : '',
                    'energy' => round($energy, 2),
                    'fuel_consumed' => round($consumed, 2),
                    'fuel_efficiency' => $fuel_efficiency, // كفاءة الوقود (kWh/لتر)
                    'avg_load' => round((float)$item->avg_load, 2), // متوسط نسبة التحميل
                    'records_count' => (int)$item->records_count, // عدد السجلات
                ];
            });

        // حساب الإحصائيات الإجمالية من جميع البيانات (بدون limit)
        $totalStatsQuery = (clone $baseQuery())
            ->whereNotNull('operation_logs.energy_produced');
        
        $totalEnergy = (float)($totalStatsQuery->sum('energy_produced') ?? 0);
        $totalFuel = (float)($totalStatsQuery->sum('fuel_consumed') ?? 0);
        $totalRecords = (int)($totalStatsQuery->count() ?? 0);
        $avgEfficiency = $totalFuel > 0 ? round($totalEnergy / $totalFuel, 2) : 0;

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
                'avg_efficiency' => $avgEfficiency,
            ],
        ];
    }

    private function getMaintenanceStats(?array $generatorIds): array
    {
        $baseQuery = function() use ($generatorIds) {
            $query = MaintenanceRecord::query();
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        $total = $baseQuery()->count();
        $thisMonth = (clone $baseQuery())->whereMonth('maintenance_date', Carbon::now()->month)
            ->whereYear('maintenance_date', Carbon::now()->year)->count();
        $totalCost = (clone $baseQuery())->sum('maintenance_cost') ?? 0;
        $totalDowntime = (clone $baseQuery())->sum('downtime_hours') ?? 0;

        return [
            'total' => $total,
            'this_month' => $thisMonth,
            'total_cost' => round($totalCost, 2),
            'total_downtime' => round($totalDowntime, 2),
        ];
    }

    private function getFuelStats(?array $generatorIds): array
    {
        $baseQuery = function() use ($generatorIds) {
            $query = FuelEfficiency::query();
            if ($generatorIds) {
                $query->whereIn('generator_id', $generatorIds);
            }
            return $query;
        };

        $total = $baseQuery()->count();
        $avgEfficiency = (clone $baseQuery())->avg('fuel_efficiency_percentage') ?? 0;
        $avgEnergyEfficiency = (clone $baseQuery())->avg('energy_distribution_efficiency') ?? 0;
        $totalCost = (clone $baseQuery())->sum('total_operating_cost') ?? 0;

        return [
            'total' => $total,
            'avg_fuel_efficiency' => round($avgEfficiency, 2),
            'avg_energy_efficiency' => round($avgEnergyEfficiency, 2),
            'total_cost' => round($totalCost, 2),
        ];
    }

    private function getComplianceStats(?array $operatorIds): array
    {
        $baseQuery = function() use ($operatorIds) {
            $query = ComplianceSafety::query();
            if ($operatorIds) {
                $query->whereIn('operator_id', $operatorIds);
            }
            return $query;
        };

        $total = $baseQuery()->count();
        $valid = (clone $baseQuery())->whereHas('safetyCertificateStatusDetail', function($q) {
            $q->where('code', 'VALID');
        })->count();
        $expired = (clone $baseQuery())->whereHas('safetyCertificateStatusDetail', function($q) {
            $q->where('code', 'EXPIRED');
        })->count();
        $pending = (clone $baseQuery())->whereHas('safetyCertificateStatusDetail', function($q) {
            $q->where('code', 'PENDING');
        })->count();

        return [
            'total' => $total,
            'valid' => $valid,
            'expired' => $expired,
            'pending' => $pending,
        ];
    }


    private function getExpiringCompliance(?array $operatorIds)
    {
        // الحصول على الشهادات المنتهية أو التي تحتاج متابعة
        $query = ComplianceSafety::with(['operator', 'safetyCertificateStatusDetail']);
        
        if ($operatorIds) {
            $query->whereIn('operator_id', $operatorIds);
        }
        
        // الحصول على IDs الثوابت
        $expiredStatusId = \App\Models\ConstantDetail::whereHas('master', function($q) {
            $q->where('constant_number', 13);
        })->where('code', 'EXPIRED')->value('id');
        
        $validStatusId = \App\Models\ConstantDetail::whereHas('master', function($q) {
            $q->where('constant_number', 13);
        })->where('code', 'VALID')->value('id');
        
        return $query->where(function ($q) use ($expiredStatusId, $validStatusId) {
                // الشهادات المنتهية
                if ($expiredStatusId) {
                    $q->where('safety_certificate_status_id', $expiredStatusId);
                }
                // أو الشهادات السارية التي لم يتم فحصها منذ أكثر من 6 أشهر
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
            ->when($expiredStatusId, function($q) use ($expiredStatusId) {
                $q->orderByRaw("CASE WHEN safety_certificate_status_id = {$expiredStatusId} THEN 0 ELSE 1 END");
            })
            ->latest('last_inspection_date')
            ->limit(5)
            ->get();
    }

    /**
     * Create notifications based on dashboard data and user role
     */
    private function createNotifications($user, ?array $operatorIds, ?array $generatorIds, $generatorsNeedingMaintenance, $expiringCompliance): void
    {
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
    }

    /**
     * Create or update notification to avoid duplicates of same type
     */
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

    /**
     * إحصائيات الدفاع المدني
     */
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
     * بيانات المهام للفني والدفاع المدني
     */
    private function getTasksData($user): array
    {
        // تضمين المولدات المحذوفة soft delete في المهام
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

        return [
            'tasks' => $tasks,
            'stats' => $stats,
        ];
    }
}
