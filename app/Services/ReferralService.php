<?php

namespace App\Services;

use App\Models\User;
use App\Models\PlatformSetting;

class ReferralService
{
    public function distributeCommissions(User $buyer, float $amount): void
    {
        // Only process if buyer has a direct referrer
        if (!$buyer->referred_by) {
            return;
        }

        $referrer = User::with('wallet')->find($buyer->referred_by);
        if (!$referrer || !$referrer->wallet) {
            return;
        }

        // ✅ Get commission rate from platform settings
        $rate = PlatformSetting::getValue('direct_reward');
        if (!$rate || $rate <= 0) {
            return;
        }

        $commissionAmount = $amount * ($rate / 100);

        // Credit to wallet
        $referrer->wallet->increment('balance', $commissionAmount);
        $referrer->wallet->increment('total_earnings', $commissionAmount);

        // Record transaction
        $referrer->transactions()->create([
            'amount' => $commissionAmount,
            'type' => 'referral_commission',
            'status' => 'completed',
            'level' => 1,
            'reference_id' => $buyer->id
        ]);

        // Record referral earnings
        $referrer->referralEarnings()->create([
            'referred_user_id' => $buyer->id,
            'level' => 1,
            'amount' => $commissionAmount,
            'type' => 'referral_commission'
        ]);
    }
}
