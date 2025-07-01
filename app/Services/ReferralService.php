<?php
namespace App\Services;

use App\Models\CommissionLevel;
use App\Models\User;

class ReferralService
{
    public function payCommissions(User $user, float $depositAmount)
    {
        $commissions = config('affiliate.referral_commissions');
        $directRewardPercentage = config('affiliate.direct_reward_percentage');

        $referrer = $user->referrer;
        $level = 1;

        // Pay direct reward (8-10% of deposits)
        if ($referrer) {
            $directReward = $depositAmount * ($directRewardPercentage / 100);

            $this->payCommission(
                $referrer,
                $user,
                $directReward,
                $level,
                'direct_reward'
            );
        }

        // Pay multi-level commissions
        while ($referrer && $level <= 3) {
            $commissionRate = $commissions[$level] ?? 0;
            $commission = $depositAmount * ($commissionRate / 100);

            $this->payCommission(
                $referrer,
                $user,
                $commission,
                $level,
                'referral_commission'
            );

            // Move to next level
            $referrer = $referrer->referrer;
            $level++;
        }
    }

    protected function payCommission($referrer, $user, $amount, $level, $type)
    {
        if ($amount <= 0) return;

        // Update referrer's wallet
        $referrer->wallet->increment('balance', $amount);
        $referrer->wallet->increment('total_earnings', $amount);

        // Record transaction
        $referrer->transactions()->create([
            'amount' => $amount,
            'type' => $type,
            'level' => $level,
            'status' => 'completed',
            'reference_id' => $user->id
        ]);

        // Record referral earnings
        $referrer->referralEarnings()->create([
            'referred_user_id' => $user->id,
            'level' => $level,
            'amount' => $amount,
            'type' => 'deposit_commission'
        ]);
    }
public function distributeCommissions(User $buyer, float $amount)
{
    $current = $buyer;
    $level = 1;

    $commissionRates = [
        1 => 0.10,
        2 => 0.05,
        3 => 0.03,
        4 => 0.02,
        5 => 0.01,
    ];
    while ($level <= 5 && $current->referred_by) {
        $referrer = User::find($current->referred_by);
        if (!$referrer) break;

        $rate = $commissionRates[$level] ?? 0;
        $commissionAmount = $amount * $rate;
        $referrer->wallet->increment('balance', $commissionAmount);
        $referrer->wallet->increment('total_earnings', $commissionAmount);

          // Record transaction
        $referrer->transactions()->create([
            'amount' => $amount,
            'type' => 'referral_commission',
            'level' => $level,
            'status' => 'completed',
            'reference_id' => $buyer->id
        ]);

        // Record referral earnings
        $referrer->referralEarnings()->create([
            'referred_user_id' => $buyer->id,
            'level' => $level,
            'amount' => $amount,
            'type' => 'referral_commission'
        ]);

        // For debugging/logging:
    //    echo "Level {$level} | {$referrer->name} earns \${$commissionAmount} from {$buyer->name}\n";

        $current = $referrer;
        $level++;
    }
}
}
