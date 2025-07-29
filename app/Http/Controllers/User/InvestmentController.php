<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InvestmentPlan;
use App\Models\PlatformSetting;
use App\Models\UserInvestment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvestmentController extends Controller
{
    public function plans(Request $request)
    {
        $user = $request->user();
        $activePlanId = optional($user->activeInvestment)->investment_plan_id;
        $data = [
            "plans" => InvestmentPlan::all(),
            "active_plan_id" => $activePlanId
        ];
        return success($data, "success");
    }

    public function activate(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:investment_plans,id'
        ]);

        $user = $request->user();
        if ($user->wallet->balance < PlatformSetting::getValue('min_deposit')) {
            return response()->json([
                'message' => 'Insufficient balance to activate plan . Minimum Wallet required: ' . PlatformSetting::getValue('min_deposit').'USDT'
            ], 422);
        }
        $plan = InvestmentPlan::findOrFail($request->plan_id);

        // If user already has an active plan and it's different from the requested one
        $currentActive = $user->activeInvestment()->first();
        if ($currentActive) {
            if ($currentActive->investment_plan_id == $plan->id) {
                return response()->json([
                    'message' => 'You already have this plan active'
                ], 422);
            }

            // Option 1: Deactivate old plan (recommended to keep history)
            $currentActive->update(['active' => false]);

            // Option 2 (alternative): delete old plan
            // $currentActive->delete();
        }

        // Activate new plan
        UserInvestment::create([
            'user_id' => $user->id,
            'investment_plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($plan->duration_days)->toDateString(),
            'active' => true,
        ]);

        return response()->json(['message' => 'Plan activated successfully']);
    }

}
