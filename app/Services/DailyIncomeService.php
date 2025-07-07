<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\PlatformSetting;

class DailyIncomeService
{
    public function calculateDailyIncome(): int
    {
        // Get settings from DB
        $commissionPercent = (float) PlatformSetting::getValue('daily_commision', 5); // default 5%
        $minDeposit = (float) PlatformSetting::getValue('min_deposit', 20); // default 20

        // Fetch users who qualify
        $users = User::has('wallet')
            ->with('wallet')
            ->get()
            ->filter(function ($user) use ($minDeposit) {
                return $user->wallet->balance >= $minDeposit;
            });

        foreach ($users as $user) {
            $income = $user->wallet->active_balance * ($commissionPercent / 100);

            // Add earnings to wallet
            $user->wallet->addEarnings($income);

            // Record transaction
            $user->transactions()->create([
                'amount' => $income,
                'type' => 'daily_income',
                'status' => 'completed'
            ]);
        }

        return $users->count();
    }
}
