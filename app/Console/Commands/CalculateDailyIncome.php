<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailyIncomeService;

class CalculateDailyIncome extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-daily-income';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(DailyIncomeService $service)
    {
        $count = $service->calculateDailyIncome();
        $this->info("Daily income calculated for {$count} users");
    }
}
