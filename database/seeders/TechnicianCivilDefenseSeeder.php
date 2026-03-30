<?php

namespace Database\Seeders;

use App\Models\Operator;
use App\Models\User;
use Illuminate\Database\Seeder;

class TechnicianCivilDefenseSeeder extends Seeder
{
    /**
     * ربط الفني بالمشغل op_test (المستخدمون يُنشأون في UserSeeder)
     */
    public function run(): void
    {
        $operator = Operator::where('name', 'op_test')->first();

        if (!$operator) {
            $this->command->warn('المشغل op_test غير موجود، تخطي ربط الفني.');
            return;
        }

        $technician = User::where('username', 't_402144398')->first();
        if ($technician) {
            $technician->operators()->syncWithoutDetaching([$operator->id]);
            $this->command->info("✓ تم ربط الفني ({$technician->username}) بالمشغل {$operator->name}");
        }
    }
}
