<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'proof',
        'rejection_reason',
        'admin_notes',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getFormattedAmountAttribute()
{
    return number_format($this->amount, 2) . ' ' .'USD';
}

}
