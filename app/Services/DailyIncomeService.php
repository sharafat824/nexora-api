<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;

class DailyIncomeService
{
    public function calculateDailyIncome(): int
    {
        $today = Carbon::today();

        // Fetch users with active investments
        $users = User::with(['wallet', 'activeInvestment.plan'])
            ->whereHas('activeInvestment', function ($query) use ($today) {
                $query->where('active', true)
                      ->whereDate('end_date', '>=', $today);
            })
            ->get();

        foreach ($users as $user) {
            $investment = $user->activeInvestment;
            $plan = $investment?->plan;

            if (!$plan) {
                continue; // no active plan
            }

            // Skip if already given today
            $alreadyGiven = $user->transactions()
                ->where('type', 'daily_income')
                ->whereDate('created_at', $today)
                ->exists();

            if ($alreadyGiven) {
                continue;
            }

            $commissionPercent = $plan->commission_percent;

            $income = $user->wallet->balance * ($commissionPercent / 100);

            // Add to wallet
            $user->wallet->addEarnings($income);

            // Log transaction
            $user->transactions()->create([
                'amount' => $income,
                'type' => 'daily_income',
                'status' => 'completed'
            ]);
        }

        return $users->count();
    }
}
