<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyIncomeController extends Controller
{
    public function collect(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // Check active plan
        $investment = $user->activeInvestment()->with('plan')->first();
        if (!$investment) {
            return response()->json(['message' => 'No active plan'], 422);
        }

        // Check if already collected today
        $already = $user->transactions()
            ->where('type', 'daily_income')
            ->whereDate('created_at', $today)
            ->exists();

        if ($already) {
            return response()->json(['message' => 'Already collected today'], 422);
        }

        $commissionPercent = $investment->plan->commission_percent;
        $income = $user->wallet->balance * ($commissionPercent / 100);

        // Update wallet
        $user->wallet->addEarnings($income);
        $user->wallet->increment('balance', $income);
        // Record transaction
        $user->transactions()->create([
            'amount' => $income,
            'type' => 'daily_income',
            'status' => 'completed'
        ]);

        return response()->json([
            'message' => 'Daily income collected successfully',
            'amount' => $income
        ]);
    }
}
