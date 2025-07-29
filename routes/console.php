<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::macro('scheduleDailyTasks', function () {
//     $this->command('app:calculate-daily-income')->dailyAt('00:00');
// });
