<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\InvestmentPlan;
use App\Http\Controllers\Controller;

class InvestmentPlanController extends Controller
{
    public function index()
    {
        $plans = InvestmentPlan::all();
        return success($plans,"success");
    }

    public function update(Request $request)
    {
        $plans = $request->input('plans', []);

        foreach ($plans as $planData) {
            $plan = InvestmentPlan::find($planData['id']);
            if ($plan) {
                $plan->update([
                    'name' => $planData['name'],
                    'commission_percent' => $planData['commission_percent'],
                    'duration_days' => $planData['duration_days'],
                ]);
            }
        }

        return response()->json(['message' => 'Investment plans updated successfully']);
    }
}
