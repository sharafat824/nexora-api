<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
     protected $fillable = [
        'user_id', 'balance', 'active_balance', 'total_earnings', 'total_withdrawals'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposit($amount)
    {
        $this->balance += $amount;
    //    $this->active_balance += $amount;
        $this->save();
    }

    public function withdraw($amount)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $this->balance -= $amount;
        $this->save();
    }

    public function addEarnings($amount)
    {
        $this->balance += $amount;
        $this->total_earnings += $amount;
        $this->save();
    }

    public function recordWithdrawal($amount)
    {
        $this->total_withdrawals += $amount;
        $this->save();
    }
}
