<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'fee', 'wallet_address', 'method', 'status', 'admin_note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNetAmountAttribute()
    {
        return $this->amount - $this->fee;
    }
}
