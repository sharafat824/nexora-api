<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateWalletTotalEarnings extends Command
{
    protected $signature = 'wallet:update-total-earnings';
    protected $description = 'Recalculate wallets.total_earnings from daily_income transactions and log updates';

    public function handle()
    {
        $this->info('Starting recalculation of total_earnings...');
        Log::info('🔄 Starting wallet total_earnings recalculation');

        // Get totals grouped by user
        $dailySums = Transaction::whereIn('type', ['daily_income', 'referral_commission'])
            ->where('status', 'completed')
            ->select('user_id', DB::raw('SUM(amount) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $updatedCount = 0;

        foreach ($dailySums as $userId => $total) {
            Wallet::where('user_id', $userId)->update(['total_earnings' => $total]);
            $msg = "Updated wallet for user_id={$userId}, total_earnings={$total}";
            $this->line($msg);
            Log::info($msg);
            $updatedCount++;
        }

        $summary = "Completed. Total wallets updated: {$updatedCount}";
        $this->info($summary);
        Log::info("✅ {$summary}");
    }
}
