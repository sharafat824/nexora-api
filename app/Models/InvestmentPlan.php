<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentPlan extends Model
{
    protected $fillable = [
        'name',
        'commission_percent',
        'duration_days'
    ];

    public function userInvestments()
    {
        return $this->hasMany(UserInvestment::class);
    }
}
