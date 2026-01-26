<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Enums\Role;
use App\Helpers\UsernameHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get super admin role
        $superAdminRole = RoleModel::where('name', 'super_admin')->first();

        $defaultPassword = 'tareq123';

        // 1. Super Admin - طارق البواب
        $superAdmin1Username = UsernameHelper::generate(Role::SuperAdmin, 'Tareq Elbawab');
        $superAdmin1 = User::updateOrCreate(
            ['email' => 'tareq@gazarased.com'],
            [
                'name' => 'طارق البواب',
                'name_en' => 'Tareq Elbawab',
                'username' => $superAdmin1Username,
                'password' => Hash::make($defaultPassword),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
                'phone' => '0592632026',
            ]
        );
        // تعيين الصلاحيات تلقائياً
        $superAdmin1->assignDefaultPermissions();

        // 2. Super Admin - أدهم أبو شملة
        $superAdmin2Username = 'sp_ashamla';
        $superAdmin2 = User::updateOrCreate(
            ['email' => 'adham@gazarased.com'],
            [
                'name' => 'أدهم أبو شملة',
                'name_en' => 'Adham Abu Shamla',
                'username' => $superAdmin2Username,
                'password' => Hash::make($defaultPassword),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
                'phone' => '0599865194',
            ]
        );
        // تعيين الصلاحيات تلقائياً
        $superAdmin2->assignDefaultPermissions();

        // 3. Super Admin - فهيم المملوك
        $superAdmin3Username = UsernameHelper::generate(Role::SuperAdmin, 'Fahim Almalook');
        $superAdmin3 = User::updateOrCreate(
            ['email' => 'fahim@gazarased.com'],
            [
                'name' => 'فهيم المملوك',
                'name_en' => 'Fahim Almalook',
                'username' => $superAdmin3Username,
                'password' => Hash::make($defaultPassword),
                'role' => Role::SuperAdmin,
                'role_id' => $superAdminRole?->id,
                'status' => 'active',
                'phone' => '0592409847',
            ]
        );
        // تعيين الصلاحيات تلقائياً
        $superAdmin3->assignDefaultPermissions();

        // 4. System User - منصة نور (for system messages)
        $systemUser = User::updateOrCreate(
            ['username' => 'platform_rased'],
            [
                'name' => 'منصة نور',
                'name_en' => 'Rased Platform',
                'email' => 'platform@gazarased.com',
                'username' => 'platform_rased',
                'password' => Hash::make('system_user_' . uniqid() . '_' . time()), // Random password, cannot login
                'role' => Role::SuperAdmin, // Use SuperAdmin role for permissions, but prevent login
                'role_id' => $superAdminRole?->id,
                'status' => 'active', // Active but cannot login
                'phone' => null,
            ]
        );

        $this->command->info('تم إنشاء المستخدمين بنجاح!');
        $this->command->info("Super Admin 1 ({$superAdmin1->name}): {$superAdmin1->username} / {$defaultPassword}");
        $this->command->info("Super Admin 2 ({$superAdmin2->name}): {$superAdmin2->username} / {$defaultPassword}");
        $this->command->info("Super Admin 3 ({$superAdmin3->name}): {$superAdmin3->username} / {$defaultPassword}");
        $this->command->info("System User ({$systemUser->name}): {$systemUser->username} (Cannot login - for system messages only)");
    }
}
