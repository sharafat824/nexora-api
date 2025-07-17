<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $fillable = ['otp', 'user_id','expires_at'];

    public function user()  {
        return $this->belongsTo(User::class);
    }
}
