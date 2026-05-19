<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Idempotent one-shot: ensure every SuperAdmin and EnergyAuthority user has
 * the four portal_requests.* permissions assigned in user_permission.
 *
 * Why a separate command (vs. re-running PermissionSeeder):
 *   - PermissionSeeder creates the permission rows but does NOT touch the
 *     user_permission pivot.
 *   - User::assignDefaultPermissions() does, but it only runs at user-create
 *     time, so existing users never see new permission rows added later.
 *
 * Safe to run repeatedly — uses syncWithoutDetaching so nothing else is
 * touched and no duplicates are produced.
 *
 * Usage:
 *   php artisan permissions:grant-portal-requests           # apply
 *   php artisan permissions:grant-portal-requests --dry-run # preview only
 */
class GrantPortalRequestsPermissions extends Command
{
    protected $signature = 'permissions:grant-portal-requests
                            {--dry-run : عرض المستخدمين المتأثرين دون تطبيق التغييرات}';

    protected $description = 'يعطي صلاحيات portal_requests.* لكل مستخدم بدور SuperAdmin أو EnergyAuthority (آمن للتشغيل المتكرر)';

    /** The permissions this command manages. */
    private const PERMISSIONS = [
        'portal_requests.view',
        'portal_requests.change_status',
        'portal_requests.create_user',
        'portal_requests.export',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // 1) Resolve the permission rows. Fail loudly if the seeder hasn't run.
        $permissions = Permission::whereIn('name', self::PERMISSIONS)->get();
        if ($permissions->count() !== count(self::PERMISSIONS)) {
            $missing = array_diff(self::PERMISSIONS, $permissions->pluck('name')->all());
            $this->error('الصلاحيات التالية غير موجودة في قاعدة البيانات: ' . implode(', ', $missing));
            $this->line('شغّل أولاً: php artisan db:seed --class=PermissionSeeder');
            return self::FAILURE;
        }
        $permissionIds = $permissions->pluck('id')->all();

        // 2) Target users: active SuperAdmin or EnergyAuthority (system role column),
        //    excluding soft-deleted. Custom-role users are NOT touched — their
        //    permissions come from their role, not direct assignment.
        $targets = User::query()
            ->whereIn('role', [Role::SuperAdmin->value, Role::EnergyAuthority->value])
            ->whereNull('deleted_at')
            ->get();

        if ($targets->isEmpty()) {
            $this->warn('لا يوجد مستخدمون بدور SuperAdmin أو EnergyAuthority.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY-RUN] ' : '') . 'المستخدمون المستهدفون: ' . $targets->count());
        $this->newLine();

        $rows = [];
        $granted = 0;

        foreach ($targets as $user) {
            $existing = $user->permissions()->whereIn('permission_id', $permissionIds)->pluck('permissions.id')->all();
            $missing  = array_diff($permissionIds, $existing);
            $needsAction = ! empty($missing);

            $rows[] = [
                'id'         => $user->id,
                'username'   => $user->username ?? '—',
                'role'       => $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role,
                'has_now'    => count($existing) . '/' . count($permissionIds),
                'will_add'   => count($missing),
                'status'     => $needsAction ? ($dryRun ? 'سيُضاف' : 'تم') : 'موجود مسبقاً',
            ];

            if ($needsAction && ! $dryRun) {
                $user->permissions()->syncWithoutDetaching($missing);
                $granted += count($missing);
            }
        }

        $this->table(
            ['#', 'username', 'role', 'الصلاحيات الحالية', 'سيُضاف', 'الحالة'],
            $rows
        );

        $this->newLine();
        if ($dryRun) {
            $this->comment('وضع المعاينة فقط — لم تُحفظ أي تغييرات. لتطبيق فعلي شغّل بدون --dry-run.');
        } else {
            $this->info("✓ اكتمل: تم إضافة {$granted} صلاحية على {$targets->count()} مستخدم.");
        }

        return self::SUCCESS;
    }
}
