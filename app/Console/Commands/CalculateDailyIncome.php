<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        Log::info('🎯 app:calculate-daily-income started');

        $count = $service->calculateDailyIncome();

        Log::info("✅ Daily income calculated for {$count} users");

        $this->info("Daily income calculated for {$count} users");
    }
}
