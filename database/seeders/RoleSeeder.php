<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'label' => 'مدير النظام',
                'description' => 'لديه جميع الصلاحيات في النظام',
                'is_system' => true,
                'order' => 1,
            ],
            [
                'name' => 'admin',
                'label' => 'مدير',
                'description' => 'مدير عادي - صلاحيات إدارية محدودة',
                'is_system' => true,
                'order' => 2,
            ],
            [
                'name' => 'energy_authority',
                'label' => 'سلطة الطاقة',
                'description' => 'دور رئيسي في النظام - يمكنه إدارة المشغلين والموظفين والفنيين والأدوار والرسائل - لديه صلاحيات واسعة على النظام',
                'is_system' => true,
                'order' => 3,
            ],
            [
                'name' => 'company_owner',
                'label' => 'مشغل',
                'description' => 'مشغل المولدات - يمكنه إدارة بياناته وموظفيه',
                'is_system' => true,
                'order' => 4,
            ],
            [
                'name' => 'employee',
                'label' => 'موظف',
                'description' => 'موظف - يمكنه تعبئة السجلات التشغيلية',
                'is_system' => true,
                'order' => 5,
            ],
            [
                'name' => 'technician',
                'label' => 'فني',
                'description' => 'فني - يمكنه تعبئة سجلات الصيانة',
                'is_system' => true,
                'order' => 6,
            ],
            [
                'name' => 'civil_defense',
                'label' => 'دفاع مدني',
                'description' => 'دفاع مدني - يمكنه تعبئة سجلات الوقاية والسلامة',
                'is_system' => true,
                'order' => 7,
            ],
        ];

        $permissions = Permission::all();

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            // Grant permissions based on role (remove old permissions first, then add new ones)
            $role->permissions()->detach();
            
            if ($role->name === 'super_admin') {
                // SuperAdmin has ALL permissions including settings, constants, logs
                $role->permissions()->attach($permissions->pluck('id'));
                
            } elseif ($role->name === 'energy_authority') {
                // EnergyAuthority (Energy Authority) - Main role in the system
                // Full permissions EXCEPT site settings, constants, logs, welcome_messages
                // Can manage users, custom roles, operators under their authority
                // Can create and update records
                // Can add operators through authorized phone numbers
                // Has access to roles and permissions definition
                // Note: welcome_messages are only for Admin and SuperAdmin
                $role->permissions()->attach($permissions->whereIn('name', [
                    // View
                    'operators.view',
                    'generators.view',
                    'generation_units.view',
                    'operation_logs.view',
                    'fuel_efficiencies.view',
                    'maintenance_records.view',
                    'compliance_safeties.view',
                    'electricity_tariff_prices.view',
                    'guide.view',
                    // SMS templates (but NOT welcome_messages - only for Admin and SuperAdmin)
                    'sms_templates.view',
                    'sms_templates.update',
                    // Create and update records
                    'operation_logs.create',
                    'operation_logs.update',
                    'maintenance_records.create',
                    'maintenance_records.update',
                    'compliance_safeties.create',
                    'compliance_safeties.update',
                    'fuel_efficiencies.create',
                    'fuel_efficiencies.update',
                    'generators.create',
                    'generators.update',
                    'generators.transfer',
                    'generation_units.create',
                    'generation_units.update',
                    // Manage users and custom roles (under their authority)
                    'users.view',
                    'users.create',
                    'users.update',
                    'users.suspend',
                    'permissions.manage',
                    'roles.view',
                    'roles.create',
                    'roles.update',
                    // Manage operators (approve/activate operators)
                    'operators.approve',
                    // Manage authorized phones (for adding operators)
                    'authorized_phones.view',
                    'authorized_phones.create',
                    'authorized_phones.update',
                    'authorized_phones.delete',
                    // Tasks management
                    'tasks.view',
                    'tasks.create',
                    'tasks.update',
                    'tasks.delete',
                ])->pluck('id'));
                
            } elseif ($role->name === 'admin') {
                // Admin - Administrative permissions including welcome_messages
                // Can view data, approve operators, and manage welcome messages
                // Note: welcome_messages are only for Admin and SuperAdmin (not for Energy Authority or Company Owner)
                $role->permissions()->attach($permissions->whereIn('name', [
                    // View only (no create, update, delete except approve)
                    'operators.view',
                    'operators.approve', // Admin can approve operators
                    'generators.view',
                    'generators.transfer',
                    'generation_units.view',
                    'operation_logs.view',
                    'fuel_efficiencies.view',
                    'maintenance_records.view',
                    'compliance_safeties.view',
                    'electricity_tariff_prices.view',
                    'guide.view',
                    // Welcome messages (only for Admin and SuperAdmin)
                    'welcome_messages.view',
                    'welcome_messages.update',
                    // Tasks management
                    'tasks.view',
                    'tasks.create',
                    'tasks.update',
                    'tasks.delete',
                ])->pluck('id'));
                
            } elseif ($role->name === 'company_owner') {
                // CompanyOwner (Operator) - Same permissions as SuperAdmin
                // EXCEPT site settings, constants, logs, and system-wide roles/users management
                // Permissions are granted by SuperAdmin through the role
                // Full access is available only after operator approval (is_approved = true)
                // Can manage their own data and users with custom roles
                $role->permissions()->attach($permissions->whereIn('name', [
                    'guide.view',
                    // Operators
                    'operators.view',
                    'operators.update',
                    // Generators
                    'generators.view',
                    'generators.create',
                    'generators.update',
                    // Generation Units
                    'generation_units.view',
                    'generation_units.create',
                    'generation_units.update',
                    'generation_units.delete',
                    // Operation Logs
                    'operation_logs.view',
                    'operation_logs.create',
                    'operation_logs.update',
                    // Fuel Efficiencies
                    'fuel_efficiencies.view',
                    'fuel_efficiencies.create',
                    'fuel_efficiencies.update',
                    // Maintenance Records
                    'maintenance_records.view',
                    'maintenance_records.create',
                    'maintenance_records.update',
                    // Compliance & Safety
                    'compliance_safeties.view',
                    'compliance_safeties.create',
                    'compliance_safeties.update',
                    // Electricity Tariff Prices
                    'electricity_tariff_prices.view',
                    'electricity_tariff_prices.create',
                    'electricity_tariff_prices.update',
                    // Manage users with custom roles (under their operator only)
                    'users.view',
                    'users.create',
                    'users.update',
                    // Manage permissions for custom role users (under their operator)
                    'permissions.manage',
                    // Can view and manage custom roles (created by Energy Authority or themselves)
                    'roles.view',
                    'roles.create',
                    'roles.update',
                ])->pluck('id'));
                
            } elseif ($role->name === 'employee') {
                // Employee - Can view and manage operation logs for assigned generators
                $role->permissions()->attach($permissions->whereIn('name', [
                    // View tasks assigned to them
                    'tasks.view',
                    'tasks.update', // Can update status of assigned tasks
                    // View and manage operation logs for assigned generators only
                    'generators.view',
                    'operation_logs.view',
                    'operation_logs.create',
                    'operation_logs.update',
                    'fuel_efficiencies.view',
                    'fuel_efficiencies.create',
                    'fuel_efficiencies.update',
                ])->pluck('id'));
                
            } elseif ($role->name === 'technician') {
                // Technician - Can only view assigned tasks and manage maintenance records for assigned generators
                $role->permissions()->attach($permissions->whereIn('name', [
                    // View tasks assigned to them
                    'tasks.view',
                    'tasks.update', // Can update status of assigned tasks
                    // View and manage maintenance records for assigned generators only
                    'generators.view',
                    'maintenance_records.view',
                    'maintenance_records.create',
                    'maintenance_records.update',
                ])->pluck('id'));
                
            } elseif ($role->name === 'civil_defense') {
                // CivilDefense - Can only view assigned tasks and manage compliance records for assigned generators
                $role->permissions()->attach($permissions->whereIn('name', [
                    // View tasks assigned to them
                    'tasks.view',
                    'tasks.update', // Can update status of assigned tasks
                    // View and manage compliance and safety records for assigned generators only
                    'generators.view',
                    'compliance_safeties.view',
                    'compliance_safeties.create',
                    'compliance_safeties.update',
                ])->pluck('id'));
            }
            // Custom roles (defined by Energy Authority or Company Owner) get permissions assigned when created
        }

        $this->command->info('تم إنشاء الأدوار بنجاح!');
    }
}
