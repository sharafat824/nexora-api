<?php
namespace App\Services;

use App\Models\User;
use App\Models\Transaction;

class DailyIncomeService
{
    public function calculateDailyIncome()
    {
        $users = User::has('wallet')->with('wallet')->get();

        foreach ($users as $user) {
            if ($user->wallet->active_balance > 0) {
                $income = $user->wallet->active_balance * 0.02; // 2% daily income

                $user->wallet->addEarnings($income);

                $user->transactions()->create([
                    'amount' => $income,
                    'type' => 'daily_income',
                    'status' => 'completed'
                ]);
            }
        }

        return count($users);
    }
}
