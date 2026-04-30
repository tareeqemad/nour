<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BackfillUserPermissions extends Command
{
    protected $signature = 'users:backfill-permissions
                            {--dry-run : عرض المستخدمين المتأثرين دون حفظ}
                            {--user= : تطبيق على username محدد فقط}';

    protected $description = 'إعادة تعيين الصلاحيات الافتراضية للمستخدمين الذين لا يملكون أي صلاحيات (مثل مشغلين أنشئوا قبل وجود assignDefaultPermissions)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $username = $this->option('user');

        $query = User::query()->with(['permissions', 'roleModel']);
        if ($username) {
            $query->where('username', $username);
        }

        $users = $query->get();
        $touched = 0;
        $skipped = 0;

        foreach ($users as $user) {
            // المستخدمون ذوو الأدوار المخصصة يأخذون صلاحياتهم من الدور — لا تتدخل
            if ($user->hasCustomRole()) {
                $skipped++;
                continue;
            }

            // SuperAdmin: لا حاجة (صلاحياته كاملة عبر isSuperAdmin())
            if ($user->isSuperAdmin()) {
                $skipped++;
                continue;
            }

            $directCount = $user->permissions->count();
            if ($directCount > 0) {
                $skipped++;
                continue;
            }

            $label = "{$user->username} ({$user->getRoleLabel()})";
            $this->line("سيتم تعيين الصلاحيات الافتراضية لـ: {$label}");

            if (! $dryRun) {
                $user->assignDefaultPermissions();
                $user->refresh();
                $newCount = $user->permissions()->count();
                $this->info("  → تم. عدد الصلاحيات الآن: {$newCount}");
            }

            $touched++;
        }

        $this->newLine();
        $action = $dryRun ? 'سيتم تحديث' : 'تم تحديث';
        $this->info("{$action} {$touched} مستخدم. تم تخطي {$skipped} مستخدم.");

        if ($dryRun) {
            $this->warn('وضع المعاينة (dry-run): لم يُحفظ أي تغيير. أعد التشغيل بدون --dry-run للتنفيذ.');
        }

        return self::SUCCESS;
    }
}
