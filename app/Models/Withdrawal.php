<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'fee', 'wallet_address', 'method', 'status','rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()  {
        return $this->hasOne(Transaction::class, 'reference_id');
    }
    public function getNetAmountAttribute()
    {
        return $this->amount - $this->fee;
    }
}
