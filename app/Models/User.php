<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;


    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'referral_code',
        'referred_by',
        'phone',
        'avatar',
        'is_active',
        'is_commission_distributed',
        'withdrawal_address',
        "country",
        "country_code",
        'is_blocked',
        'kyc_status',
        'kyc_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }
    public function recentLoginLogs()
    {
        return $this->hasMany(LoginLog::class)->latest('logged_in_at')->limit(5);
    }


    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function referralEarnings()
    {
        return $this->hasMany(ReferralEarning::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->referral_code = self::generateReferralCode();
        });

        static::created(function ($user) {
            $user->wallet()->create([
                'balance' => 0, // Signup bonus
                'active_balance' => 0
            ]);

            // $user->transactions()->create([
            //     'amount' => 10.00,
            //     'type' => 'signup_bonus',
            //     'status' => 'completed'
            // ]);

            // if ($user->referrer) {
            //     $user->referrer->referralEarnings()->create([
            //         'referred_user_id' => $user->id,
            //         'level' => 1,
            //         'amount' => 10.00, // Direct reward (10% of $100 deposit equivalent)
            //         'type' => 'signup'
            //     ]);
            // }
        });
    }

    private static function generateReferralCode()
    {
        $code = strtoupper(Str::random(8));
        if (self::where('referral_code', $code)->exists()) {
            return self::generateReferralCode();
        }
        return $code;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function referralTeamWithLevels(int $maxLevel = 5)
    {
        $team = [];
        $currentLevelUsers = [$this->id];

        for ($level = 1; $level <= $maxLevel; $level++) {
            $nextLevelUsers = self::whereIn('referred_by', $currentLevelUsers)->get();

            if ($nextLevelUsers->isEmpty())
                break;

            foreach ($nextLevelUsers as $user) {
                $team[] = [
                    'user' => $user,
                    'level' => $level
                ];
            }

            $currentLevelUsers = $nextLevelUsers->pluck('id')->toArray();
        }

        return $team;
    }

    public function referralTeamWithCommission(int $maxLevel = 5)
    {
        $team = [];
        $levelMap = []; // user_id => level
        $allIds = [];
        $currentLevelIds = [$this->id];

        // BFS to collect IDs per level
        for ($level = 1; $level <= $maxLevel; $level++) {
            $ids = self::whereIn('referred_by', $currentLevelIds)->pluck('id')->all();
            if (empty($ids))
                break;

            foreach ($ids as $id)
                $levelMap[$id] = $level;
            $allIds = array_merge($allIds, $ids);
            $currentLevelIds = $ids;
        }

        if (empty($allIds))
            return [];

        // Get user info in one query
        $users = self::whereIn('id', $allIds)
            ->get(['id', 'name', 'email', 'created_at'])
            ->keyBy('id');

        // What *I* earned from each referred user (single grouped query)
        $totals = ReferralEarning::query()
            ->where('user_id', $this->id)
            ->whereIn('referred_user_id', $allIds)
            // ->where('status', 'completed') // if applicable
            ->selectRaw('referred_user_id, SUM(amount) AS total_commission, MAX(created_at) AS last_earning_at')
            ->groupBy('referred_user_id')
            ->get()
            ->keyBy('referred_user_id');

        foreach ($allIds as $id) {
            $u = $users[$id];
            $sum = (float) ($totals[$id]->total_commission ?? 0);
            $team[] = [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'level' => $levelMap[$id],
                'commission' => $sum,
                'last_earning_at' => isset($totals[$id]) ? (string) $totals[$id]->last_earning_at : null,
                'joined_at' => $u->created_at->format('Y-m-d H:i:s'),
            ];
        }

        // Optional: sort by commission desc
   //     usort($team, fn($a, $b) => $b['commission'] <=> $a['commission']);

        return $team;
    }
    public function getTeamWithCommissionAttribute()
    {
        return $this->referralTeamWithCommission();
    }

    public function getTeamAttribute()
    {
        return $this->referralTeamWithLevels();
    }

    public function referralCommissionByLevel()
    {
        return $this->referralEarnings()
            ->whereNotNull('level')
            ->selectRaw('level, SUM(amount) as total_earned')
            ->groupBy('level')
            ->pluck('total_earned', 'level')
            ->toArray();
    }

    public function dailyEarnings($fromDate = null, $toDate = null)
    {
        $query = $this->transactions()
            ->where('type', 'daily_income')
            ->where('status', 'completed');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query->orderByDesc('created_at')
            ->get(['amount', 'created_at']);
    }

    public function activeInvestment()
    {
        return $this->hasOne(UserInvestment::class)
            ->where('active', true)
            ->whereDate('end_date', '>=', now());
    }


    public function totalInvestment($maxLevel = 5)
    {
        // Collect all user IDs referrals
        $ids = [];
        $currentLevelUsers = [$this->id];

        for ($level = 1; $level <= $maxLevel; $level++) {
            $nextLevelUsers = self::whereIn('referred_by', $currentLevelUsers)
                ->pluck('id')
                ->toArray();

            if (empty($nextLevelUsers))
                break;

            $ids = array_merge($ids, $nextLevelUsers);
            $currentLevelUsers = $nextLevelUsers;
        }

        // Sum only completed deposits
        return \App\Models\Deposit::whereIn('user_id', $ids)
            ->where('status', 'completed')
            ->sum('amount');
    }

}
