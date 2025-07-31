<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'active_balance',
        'total_earnings',
        'total_withdrawals'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposit($amount, $reason = null)
    {
        $this->balance += $amount;
        $this->save();

        // Automatically log a transaction if reason is provided
        if ($reason) {
            $this->user->transactions()->create([
                'amount' => $amount,
                'type' => 'refund',
                'status' => 'completed',
                'description' => $reason,
            ]);
        }
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
      //  $this->balance += $amount;
        $this->total_earnings += $amount;
        $this->save();
    }

    public function recordWithdrawal($amount)
    {
        $this->total_withdrawals += $amount;
        $this->save();
    }
}
