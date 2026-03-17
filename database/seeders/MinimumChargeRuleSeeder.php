<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MinimumChargeRule;

class MinimumChargeRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['ampere' =>  2, 'phase_type' => 1, 'minimum_charge' =>  10.00],
            ['ampere' =>  2, 'phase_type' => 2, 'minimum_charge' =>  30.00],
            ['ampere' =>  6, 'phase_type' => 1, 'minimum_charge' =>  20.00],
            ['ampere' =>  6, 'phase_type' => 2, 'minimum_charge' =>  60.00],
            ['ampere' => 10, 'phase_type' => 1, 'minimum_charge' =>  50.00],
            ['ampere' => 10, 'phase_type' => 2, 'minimum_charge' => 150.00],
            ['ampere' => 16, 'phase_type' => 1, 'minimum_charge' =>  50.00],
            ['ampere' => 16, 'phase_type' => 2, 'minimum_charge' => 300.00],
        ];

        foreach ($rules as $rule) {
            MinimumChargeRule::updateOrCreate(
                ['ampere' => $rule['ampere'], 'phase_type' => $rule['phase_type']],
                ['minimum_charge' => $rule['minimum_charge']]
            );
        }

        MinimumChargeRule::clearCache();
    }
}
