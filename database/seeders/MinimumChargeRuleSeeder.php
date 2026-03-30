<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MinimumChargeRule;
use App\Models\Operator;

class MinimumChargeRuleSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء القواعد الافتراضية (قيمة 0) لكل مشغل حسب قيم الأمبير من الثوابت
        $operatorIds = Operator::pluck('id');

        foreach ($operatorIds as $operatorId) {
            MinimumChargeRule::seedForOperator($operatorId);
        }
    }
}
