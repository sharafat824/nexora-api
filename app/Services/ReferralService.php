<?php
namespace App\Services;

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

    public function getTeamStats(User $user)
    {
        $stats = [];

        for ($level = 1; $level <= 3; $level++) {
            $team = $this->getLevel($user, $level);

            $stats[] = [
                'level' => $level,
                'count' => $team->count(),
                'total_investment' => $team->sum(function($member) {
                    return $member->deposits()->where('status', 'completed')->sum('amount');
                }),
                'earnings' => $user->referralEarnings()
                    ->where('level', $level)
                    ->sum('amount')
            ];
        }

        return $stats;
    }

    public function getTeamMembers(User $user, $level = 1)
    {
        $members = $this->getLevel($user, $level)->load('wallet', 'deposits');

        return $members->map(function($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'username' => $member->username,
                'joined_at' => $member->created_at->format('Y-m-d'),
                'investment' => $member->deposits()
                    ->where('status', 'completed')
                    ->sum('amount'),
                'wallet_balance' => $member->wallet->balance
            ];
        });
    }

    protected function getLevel(User $user, $level, $currentLevel = 1)
    {
        if ($currentLevel == $level) {
            return $user->referrals;
        }

        $team = collect();

        foreach ($user->referrals as $referral) {
            $team = $team->merge($this->getLevel($referral, $level, $currentLevel + 1));
        }

        return $team;
    }
}
