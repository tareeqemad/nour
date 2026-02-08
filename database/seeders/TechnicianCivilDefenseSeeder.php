<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\Role as RoleModel;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TechnicianCivilDefenseSeeder extends Seeder
{
    /**
     * إنشاء يوزر فني ويوزر دفاع مدني مع صلاحياتهم
     */
    public function run(): void
    {
        $technicianRole = RoleModel::where('name', 'technician')->first();
        $civilDefenseRole = RoleModel::where('name', 'civil_defense')->first();

        if (!$technicianRole || !$civilDefenseRole) {
            $this->command->error('يجب تشغيل RoleSeeder أولاً!');
            return;
        }

        $operator = Operator::where('name', 'op_test')->first();

        // يوزر فني - مرتبط بالمشغل op_test (صلاحيات سجلات الصيانة فقط)
        $technicianUser = User::updateOrCreate(
            ['username' => 't_402144398'],
            [
                'name' => 'فني الاختبار',
                'name_en' => 'Test Technician',
                'email' => 'technician@gazarased.com',
                'username' => 't_402144398',
                'password' => Hash::make('12345678'),
                'role' => Role::Technician,
                'role_id' => $technicianRole->id,
                'status' => 'active',
                'phone' => '0597777777',
            ]
        );
        if ($operator) {
            $technicianUser->operators()->syncWithoutDetaching([$operator->id]);
        }
        $this->command->info("تم إنشاء يوزر الفني: t_402144398 / 12345678");

        // يوزر دفاع مدني - صلاحيات سجلات الوقاية والسلامة فقط
        User::updateOrCreate(
            ['username' => 'cd_402144398'],
            [
                'name' => 'دفاع مدني الاختبار',
                'name_en' => 'Test Civil Defense',
                'email' => 'civildefense@gazarased.com',
                'username' => 'cd_402144398',
                'password' => Hash::make('12345678'),
                'role' => Role::CivilDefense,
                'role_id' => $civilDefenseRole->id,
                'status' => 'active',
                'phone' => '0596666666',
            ]
        );
        $this->command->info("تم إنشاء يوزر الدفاع المدني: cd_402144398 / 12345678");
    }
}
