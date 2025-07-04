<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlatformSetting;
use App\Models\CommissionLevel;

class SettingsAndCommissionsSeeder extends Seeder
{
    public function run(): void
    {
        // General Platform Settings
        $generalSettings = [
            [
                'key' => 'site_name',
                'value' => 'Nexora',
                'display_name' => 'Noxora',
                'group' => 'general',
                'description' => 'The name of the platform displayed throughout the site.',
            ],
            [
                'key' => 'support_email',
                'value' => 'nexora223344@gmail.com',
                'display_name' => 'Support Email',
                'group' => 'general',
                'description' => 'Support contact email for users.',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => false,
                'display_name' => 'Maintenance Mode',
                'group' => 'general',
                'description' => 'Toggle to enable or disable the site.',
            ],
            [
                'key' => 'daily_commision',
                'value' => '5',
                'display_name' => 'Daily Commission',
                'group' => 'general',
                'description' => 'Users daily commission.',
            ],
        ];

        foreach ($generalSettings as $setting) {
            PlatformSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // Referral Commission Levels
        $commissionLevels = [
            // // Signup Commissions
            ['level' => 1, 'percentage' => 8.00, 'type' => 'signup'],
            ['level' => 2, 'percentage' => 5.00, 'type' => 'signup'],
            ['level' => 3, 'percentage' => 3.00, 'type' => 'signup'],
            ['level' => 4, 'percentage' => 2.00, 'type' => 'signup'],
            ['level' => 5, 'percentage' => 1.00, 'type' => 'signup'],

            // Deposit Commissions
            // ['level' => 1, 'percentage' => 7.00, 'type' => 'deposit'],
            // ['level' => 2, 'percentage' => 4.00, 'type' => 'deposit'],
            // ['level' => 3, 'percentage' => 2.50, 'type' => 'deposit'],
            // ['level' => 4, 'percentage' => 1.50, 'type' => 'deposit'],
            // ['level' => 5, 'percentage' => 1.00, 'type' => 'deposit'],
        ];

        foreach ($commissionLevels as $commission) {
            CommissionLevel::updateOrCreate(
                ['level' => $commission['level'], 'type' => $commission['type']],
                ['percentage' => $commission['percentage']]
            );
        }
    }
}
