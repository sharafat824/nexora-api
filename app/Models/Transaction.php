<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'type', 'status', 'reference', 'description', 'reference_id', 'level'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referenceUser()
{
    return $this->belongsTo(User::class, 'reference_id');
}
}
