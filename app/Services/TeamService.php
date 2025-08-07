<?php

namespace App\Services;

use App\Models\User;
use App\Models\CommissionLevel;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function distributeTeamReward(User $user, float $amount): void
    {
        DB::transaction(function () use ($user, $amount) {
            $current = $user;
            $level = 1;

            // Fetch commission rates from DB for type 'deposit' and index by level
            $commissionRates = CommissionLevel::where('type', 'team_reward')
                ->pluck('percentage', 'level')
                ->map(fn($value) => $value / 100); // Convert percentage to decimal
            while ($level <= 5 && $current->referred_by) {
                $referrer = User::with('wallet')->find($current->referred_by);
                if (!$referrer || !$referrer->wallet)
                    break;

                $rate = $commissionRates[$level] ?? 0;
                if ($rate > 0) {
                    $commissionAmount = $amount * $rate;

                    // Credit to wallet
                    $referrer->wallet->increment('balance', $commissionAmount);
                    $referrer->wallet->increment('total_earnings', $commissionAmount);

                    // Record transaction
                    $referrer->transactions()->create([
                        'amount' => $commissionAmount,
                        'type' => 'team_reward',
                        'status' => 'completed',
                        'level' => $level,
                        'reference_id' => $user->id
                    ]);

                    // Record referral earnings
                    $referrer->referralEarnings()->create([
                        'referred_user_id' => $user->id,
                        'level' => $level,
                        'amount' => $commissionAmount,
                        'type' => 'team_reward'
                    ]);
                }

                $current = $referrer;
                $level++;
            }
        });
    }
}
