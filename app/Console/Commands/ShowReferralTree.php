<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ShowReferralTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    protected $signature = 'referral:tree {userId}';
    protected $description = 'Show 5-level referral tree of a user';

    public function handle()
    {
        $user = User::find($this->argument('userId'));
        if (!$user) {
            $this->error('User not found');
            return;
        }

        $this->info("Referral tree for: {$user->name}");
        $this->line('----------------------------------');

        $currentLevelUsers = [$user->id];
        for ($level = 1; $level <= 5; $level++) {
            $nextLevelUsers = User::whereIn('referred_by', $currentLevelUsers)->get();

            if ($nextLevelUsers->isEmpty())
                break;

            foreach ($nextLevelUsers as $u) {
                $this->line("Level $level: {$u->name}");
            }

            $currentLevelUsers = $nextLevelUsers->pluck('id')->toArray();
        }
    }
}
