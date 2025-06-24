<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionLevel extends Model
{
     protected $fillable = ['level', 'percentage', 'type'];

    public static function getLevels($type)
    {
        return self::where('type', $type)
            ->orderBy('level')
            ->get()
            ->pluck('percentage', 'level')
            ->toArray();
    }

    public static function updateLevels($type, array $levels)
    {
        self::where('type', $type)->delete();

        foreach ($levels as $level => $percentage) {
            self::create([
                'level' => $level,
                'percentage' => $percentage,
                'type' => $type
            ]);
        }
    }
}
