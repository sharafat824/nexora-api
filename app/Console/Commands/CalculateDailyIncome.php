<?php

namespace App\Console\Commands;

use App\Models\PlatformSetting;
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
        $enabled = PlatformSetting::getValue('enable_daily_earning', 0);

        // Strictly check value
        if ((string) $enabled !== '1') {
            Log::info('⚠️ Daily income calculation skipped (disabled in settings)');
            $this->warn('Daily income calculation skipped (disabled in settings)');
            return;
        }



        $count = $service->calculateDailyIncome();

        Log::info("✅ Daily income calculated for {$count} users");

        $this->info("Daily income calculated for {$count} users");
    }
}
