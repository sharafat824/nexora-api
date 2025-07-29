<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserInvestment extends Model
{
    protected $fillable = [
        'user_id',
        'investment_plan_id',
        'start_date',
        'end_date',
        'active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(InvestmentPlan::class, 'investment_plan_id');
    }

    public function isActive(): bool
    {
        return $this->active && Carbon::parse($this->end_date)->isFuture();
    }
}
