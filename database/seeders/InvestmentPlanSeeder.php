<?php

namespace Database\Seeders;

use App\Models\InvestmentPlan;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InvestmentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            ['name' => 'Core', 'commission_percent' => 1.5, 'duration_days' => 90],
            ['name' => 'Elite', 'commission_percent' => 2.0, 'duration_days' => 180],
            ['name' => 'Premier', 'commission_percent' => 3.0, 'duration_days' => 365],
        ];

        foreach ($plans as $plan) {
            InvestmentPlan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
