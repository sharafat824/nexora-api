<?php

namespace App\Services;

use App\Models\User;
use App\Models\CommissionLevel;

class ReferralService
{
    public function distributeCommissions(User $buyer, float $amount): void
    {
        $current = $buyer;
        $level = 1;

        // Fetch commission rates from DB for type 'deposit' and index by level
        $commissionRates = CommissionLevel::where('type', 'signup')
            ->pluck('percentage', 'level')
            ->map(fn ($value) => $value / 100); // Convert percentage to decimal

        while ($level <= 5 && $current->referred_by) {
            $referrer = User::find($current->referred_by);
            if (!$referrer) break;

            $rate = $commissionRates[$level] ?? 0;
            if ($rate > 0) {
                $commissionAmount = $amount * $rate;

                // Credit to wallet
                $referrer->wallet->increment('balance', $commissionAmount);
                $referrer->wallet->increment('total_earnings', $commissionAmount);

                // Record transaction
                $referrer->transactions()->create([
                    'amount' => $commissionAmount,
                    'type' => 'referral_commission',
                    'status' => 'completed',
                    'level' => $level,
                    'reference_id' => $buyer->id
                ]);

                // Record referral earnings
                $referrer->referralEarnings()->create([
                    'referred_user_id' => $buyer->id,
                    'level' => $level,
                    'amount' => $commissionAmount,
                    'type' => 'referral_commission'
                ]);
            }

            $current = $referrer;
            $level++;
        }
    }
}
