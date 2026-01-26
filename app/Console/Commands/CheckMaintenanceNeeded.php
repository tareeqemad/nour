<?php

namespace App\Console\Commands;

use App\Models\Generator;
use App\Models\Notification;
use App\Models\User;
use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckMaintenanceNeeded extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:check 
                            {--days=180 : عدد الأيام منذ آخر صيانة (افتراضي: 180 يوم = 6 أشهر)}
                            {--upcoming : إرسال تذكير للمولدات القريبة من الصيانة (5 أشهر)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'التحقق من المولدات التي تحتاج صيانة وإرسال الإشعارات';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $checkUpcoming = $this->option('upcoming');

        $this->info("بدء التحقق من المولدات التي تحتاج صيانة...");

        // 1. المولدات التي تجاوزت المدة المحددة
        $generatorsNeedingMaintenance = $this->getGeneratorsNeedingMaintenance($days);
        
        // 2. المولدات القريبة من الصيانة (اختياري)
        $upcomingMaintenance = [];
        if ($checkUpcoming) {
            $upcomingMaintenance = $this->getUpcomingMaintenanceGenerators();
        }

        // 3. إرسال الإشعارات
        $this->sendNotifications($generatorsNeedingMaintenance, $upcomingMaintenance);

        $this->info("تم التحقق من {$generatorsNeedingMaintenance->count()} مولد يحتاج صيانة فورية");
        
        if ($checkUpcoming && count($upcomingMaintenance) > 0) {
            $this->info("تم التحقق من " . count($upcomingMaintenance) . " مولد يحتاج صيانة قريباً");
        }

        return Command::SUCCESS;
    }

    /**
     * جلب المولدات التي تحتاج صيانة فورية
     *
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getGeneratorsNeedingMaintenance(int $days)
    {
        return Generator::with(['generationUnit.operator', 'operator'])
            ->where(function ($query) use ($days) {
                // لم يتم صيانة أبداً
                $query->whereNull('last_major_maintenance_date')
                    // أو تجاوزت المدة المحددة
                    ->orWhere('last_major_maintenance_date', '<', Carbon::now()->subDays($days));
            })
            ->whereHas('statusDetail', function ($q) {
                // فقط المولدات النشطة
                $q->where('code', 'ACTIVE');
            })
            ->get();
    }

    /**
     * جلب المولدات القريبة من الصيانة (تذكير مسبق)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUpcomingMaintenanceGenerators()
    {
        // المولدات التي بين 5 و 6 أشهر (تذكير قبل شهر)
        $fiveMonthsAgo = Carbon::now()->subMonths(5);
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        return Generator::with(['generationUnit.operator', 'operator'])
            ->whereNotNull('last_major_maintenance_date')
            ->where('last_major_maintenance_date', '>=', $sixMonthsAgo)
            ->where('last_major_maintenance_date', '<', $fiveMonthsAgo)
            ->whereHas('statusDetail', function ($q) {
                $q->where('code', 'ACTIVE');
            })
            ->get();
    }

    /**
     * إرسال الإشعارات
     *
     * @param \Illuminate\Database\Eloquent\Collection $generatorsNeedingMaintenance
     * @param array $upcomingMaintenance
     * @return void
     */
    private function sendNotifications($generatorsNeedingMaintenance, array $upcomingMaintenance): void
    {
        // 1. إشعارات للمولدات التي تحتاج صيانة فورية
        foreach ($generatorsNeedingMaintenance as $generator) {
            $operator = $this->getGeneratorOperator($generator);
            
            if ($operator) {
                // إشعار للمشغل (CompanyOwner)
                Notification::notifyOperatorUsers(
                    $operator,
                    'maintenance_needed',
                    'مولد يحتاج صيانة فورية',
                    "المولد {$generator->name} يحتاج إلى صيانة فورية. آخر صيانة: " . 
                    ($generator->last_major_maintenance_date 
                        ? $generator->last_major_maintenance_date->format('Y-m-d')
                        : 'لم يتم صيانة'),
                    route('admin.maintenance-records.create', ['generator_id' => $generator->id])
                );
            }
        }

        // 2. إشعارات للمولدات القريبة من الصيانة
        foreach ($upcomingMaintenance as $generator) {
            $operator = $this->getGeneratorOperator($generator);
            
            if ($operator) {
                Notification::notifyOperatorUsers(
                    $operator,
                    'maintenance_warning',
                    'تذكير: مولد يحتاج صيانة قريباً',
                    "المولد {$generator->name} يحتاج إلى صيانة قريباً. آخر صيانة: " . 
                    $generator->last_major_maintenance_date->format('Y-m-d'),
                    route('admin.maintenance-records.create', ['generator_id' => $generator->id])
                );
            }
        }

        // 3. إشعار ملخص لسلطة الطاقة والسوبر ادمن والادمن
        $count = $generatorsNeedingMaintenance->count();
        if ($count > 0) {
            $managers = User::whereIn('role', [
                Role::SuperAdmin->value,
                Role::Admin->value,
                Role::EnergyAuthority->value,
            ])->get();

            foreach ($managers as $manager) {
                // حذف الإشعارات القديمة من نفس النوع
                Notification::where('user_id', $manager->id)
                    ->where('type', 'maintenance_needed_summary')
                    ->where('read', false)
                    ->delete();

                // إنشاء إشعار جديد
                Notification::createNotification(
                    $manager->id,
                    'maintenance_needed_summary',
                    'مولدات تحتاج صيانة',
                    "يوجد {$count} مولد يحتاج إلى صيانة فورية",
                    route('admin.maintenance-records.index')
                );
            }
        }
    }

    /**
     * الحصول على المشغل للمولد
     *
     * @param Generator $generator
     * @return \App\Models\Operator|null
     */
    private function getGeneratorOperator(Generator $generator)
    {
        // محاولة الحصول على المشغل من generationUnit
        if ($generator->generationUnit && $generator->generationUnit->operator) {
            return $generator->generationUnit->operator;
        }

        // Fallback: المشغل المباشر
        if ($generator->operator) {
            return $generator->operator;
        }

        return null;
    }
}
