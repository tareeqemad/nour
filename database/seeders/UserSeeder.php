<?php

namespace Database\Seeders;

use App\Models\Role as RoleModel;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole   = RoleModel::where('name', 'super_admin')->first();
        $energyAuthRole   = RoleModel::where('name', 'energy_authority')->first();
        $companyOwnerRole = RoleModel::where('name', 'company_owner')->first();

        $users = [
            // Super Admins
            [
                'email'    => 'tareq@gazarased.com',
                'name'     => 'طارق البواب',
                'name_en'  => 'Tareq Elbawab',
                'username' => 'sp_telbawab',
                'phone'    => '0592632026',
                'password' => '$2y$12$rDMX1aHohFo/oYxLou53OOcfN8.G3kMqqkCPFah6fyC0MycbP/Wwq',
                'role'     => Role::SuperAdmin,
                'role_id'  => $superAdminRole?->id,
            ],
            [
                'email'    => 'adham@gazarased.com',
                'name'     => 'أدهم أبو شملة',
                'name_en'  => 'Adham Abu Shamla',
                'username' => 'sp_ashamla',
                'phone'    => '0599865194',
                'password' => '$2y$12$pjAVzPIBQPAGxGr3zx5sF.8YyWj2/BcZe7oZmgso7vIEt5Vei/Cu.',
                'role'     => Role::SuperAdmin,
                'role_id'  => $superAdminRole?->id,
            ],
            [
                'email'    => 'fahim@gazarased.com',
                'name'     => 'فهيم المملوك',
                'name_en'  => 'Fahim Almalook',
                'username' => 'sp_falmalook',
                'phone'    => '0592409847',
                'password' => '$2y$12$o6z2DVuTajH1FYuRftwe7OsX7XMl1/BTMBREVX5mrktU2PlaesCs6',
                'role'     => Role::SuperAdmin,
                'role_id'  => $superAdminRole?->id,
            ],
            [
                'email'    => 'sp_msabah@gazarased.com',
                'name'     => 'ميرفت الصباح',
                'name_en'  => 'Mervat Al Sabah',
                'username' => 'sp_msabah',
                'phone'    => '0597238383',
                'password' => '$2y$12$2GYVCvdhqbYy8nWo/gEE9uVYSvyt8Ka1Bmdd3AXPvUcUGPdnqLo7G',
                'role'     => Role::SuperAdmin,
                'role_id'  => $superAdminRole?->id,
            ],
            // System User
            [
                'email'    => 'platform@gazarased.com',
                'name'     => 'منصة نور',
                'name_en'  => 'Rased Platform',
                'username' => 'platform_rased',
                'phone'    => null,
                'password' => '$2y$12$fg2QSgWKWfOqC/ENncN5MeZpcvVbJ6wmBSitvZVEEZSjKUW1Cfdy2',
                'role'     => Role::SuperAdmin,
                'role_id'  => $superAdminRole?->id,
            ],
            // Energy Authority
            [
                'email'    => 'ea_mmahdi@gazarased.com',
                'name'     => 'محمد مهدي',
                'name_en'  => 'Mohammad Mahdi',
                'username' => 'ea_mmahdi',
                'phone'    => null,
                'password' => '$2y$12$0LeMW.Peuu.D6nfSqaIT1OBA9CRcS4fs7e9AweM6PcZPR8io4DHLy',
                'role'     => Role::EnergyAuthority,
                'role_id'  => $energyAuthRole?->id,
            ],
            [
                'email'    => 'ea_ahamreen@gazarased.com',
                'name'     => 'أحمد ابو العمرين',
                'name_en'  => 'Ahmad Abu Alamreen',
                'username' => 'ea_ahamreen',
                'phone'    => null,
                'password' => '$2y$12$PHMwJPI7PtBkpJMkaDgjIOHCaTQLsPqpHVqYG5WJ.0fV4saDIVnqe',
                'role'     => Role::EnergyAuthority,
                'role_id'  => $energyAuthRole?->id,
            ],
            // Company Owners
            [
                'email'    => 'op_nour1@gazarased.com',
                'name'     => 'مشغل نور 1',
                'name_en'  => 'Nour Operator 1',
                'username' => 'op_nour1',
                'phone'    => null,
                'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                'role'     => Role::CompanyOwner,
                'role_id'  => $companyOwnerRole?->id,
            ],
        ];

        foreach ($users as $data) {
            $password = $data['password'];
            unset($data['password']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => $password,
                    'status'   => 'active',
                ])
            );

            $user->assignDefaultPermissions();
            $this->command->info("✓ {$user->name} ({$user->username})");
        }

        $this->command->info('تم إنشاء/تحديث المستخدمين بنجاح!');
    }
}
