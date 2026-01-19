<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * توليد username بناءً على الآلية الجديدة
     * SuperAdmin: sp_ + الحرف الأول + اسم العائلة
     * Admin: ad_ + الحرف الأول + اسم العائلة  
     * EnergyAuthority: ea_ + الحرف الأول + اسم العائلة
     * CompanyOwner: co_ + الحرف الأول + اسم العائلة
     */
    private function generateUsername(string $nameEn, Role $role): string
    {
        // تقسيم الاسم إلى كلمات
        $nameParts = preg_split('/[\s\-_]+/', trim($nameEn));
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[count($nameParts) - 1] ?? $firstName;

        // الحصول على الحرف الأول من الاسم الأول
        $firstChar = mb_substr($firstName, 0, 1, 'UTF-8');
        $firstChar = mb_strtolower($firstChar, 'UTF-8');

        // تنظيف اسم العائلة
        $cleanLastName = preg_replace('/[^a-zA-Z0-9]/', '', $lastName);
        $cleanLastName = mb_strtolower($cleanLastName, 'UTF-8');

        // تحديد البادئة حسب الدور
        $prefix = match($role) {
            Role::SuperAdmin => 'sp_',
            Role::EnergyAuthority => 'ea_',
            Role::CompanyOwner => 'co_',
            default => 'ad_',
        };

        // username = prefix + first_char + last_name
        $usernameBase = $prefix . $firstChar . $cleanLastName;

        // التأكد من أن username فريد
        $counter = 1;
        $username = $usernameBase;
        while (User::where('username', $username)->whereNull('deleted_at')->exists()) {
            $username = $usernameBase . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get super admin role
        $superAdminRole = RoleModel::where('name', 'super_admin')->first();

        $defaultPassword = 'tareq123';

        // 1. Super Admin - طارق البواب
        $superAdmin1Username = $this->generateUsername('Tareq Elbawab', Role::SuperAdmin);
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
        $superAdmin3Username = $this->generateUsername('Fahim Almalook', Role::SuperAdmin);
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

        // 4. System User - منصة راصد (for system messages)
        $systemUser = User::updateOrCreate(
            ['username' => 'platform_rased'],
            [
                'name' => 'منصة راصد',
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
