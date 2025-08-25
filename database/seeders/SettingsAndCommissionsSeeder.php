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
                'display_name' => 'Nexora',
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
                'key' => 'site_logo',
                'value' => '/storage/logos/default-logo.png',
                'display_name' => 'Site Logo',
                'group' => 'general',
                'description' => 'Logo for the platform.',
            ],
            [
                'key' => 'privacy_policy_url',
                'value' => '/privacy-policy',
                'display_name' => 'Privacy Policy Link',
                'group' => 'general',
                'description' => 'Link to privacy policy page.',
            ],
            [
                'key' => 'terms_policy_url',
                'value' => '/terms',
                'display_name' => 'Terms & Conditions Link',
                'group' => 'general',
                'description' => 'Link to terms and conditions page.',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => false,
                'display_name' => 'Maintenance Mode',
                'group' => 'general',
                'description' => 'Toggle to enable or disable the site.',
            ],
            [
                'key' => 'enable_daily_earning',
                'value' => true,
                'display_name' => 'Enable Daily Earning',
                'group' => 'general',
                'description' => 'Enable or disable daily earning feature for users.',
            ],
            // [
            //     'key' => 'daily_commision',
            //     'value' => '5',
            //     'display_name' => 'Daily Commission (%)',
            //     'group' => 'general',
            //     'description' => 'Users daily commission percentage.',
            // ],
            [
                'key' => 'direct_reward',
                'value' => '5',
                'display_name' => 'Direct Reward (%)',
                'group' => 'general',
                'description' => 'Users direct commission percentage.',
            ],
            [
                'key' => 'min_deposit',
                'value' => 20,
                'display_name' => "Minimum Deposit",
                'group' => 'general',
                'description' => 'Minimum deposit allowed'
            ],
            [
                'key' => 'min_withdraw',
                'value' => 20,
                'display_name' => "Minimum Withdraw",
                'group' => 'general',
                'description' => 'Minimum withdrawal allowed'
            ],
            [
                'key' => 'withdraw_fee',
                'value' => '5', // percent
                'display_name' => 'Withdrawal Fee (%)',
                'group' => 'general',
                'description' => 'Percentage fee charged on each withdrawal.'
            ],
            [
                'key' => 'withdrawal_limit_daily',
                'value' => 1000,
                'display_name' => 'Daily Withdrawal Limit',
                'group' => 'general',
                'description' => 'Maximum amount a user can withdraw in a single day.'
            ],
            [
                'key' => 'withdrawal_limit_monthly',
                'value' => 20000,
                'display_name' => 'Monthly Withdrawal Limit',
                'group' => 'general',
                'description' => 'Maximum amount a user can withdraw in a month.'
            ],
            [
                'key' => 'admin_wallet_address',
                'value' => 'TXYZ1234567890', // Example USDT wallet address
                'display_name' => 'Admin Wallet Address',
                'group' => 'general',
                'description' => 'USDT wallet address for admin withdrawals.'
            ],
            [
                'key' => 'telegram_link',
                "value" => '',
                'display_name' => 'Telegram Link',
                'group' => 'general',
                'description' => 'Telegram link.'
            ]
        ];

        foreach ($generalSettings as $setting) {
            PlatformSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // Referral Commission Levels
        $commissionLevels = [
            ['level' => 1, 'percentage' => 8.00, 'type' => 'team_reward'],
            ['level' => 2, 'percentage' => 5.00, 'type' => 'team_reward'],
            ['level' => 3, 'percentage' => 3.00, 'type' => 'team_reward'],
            ['level' => 4, 'percentage' => 2.00, 'type' => 'team_reward'],
            ['level' => 5, 'percentage' => 1.00, 'type' => 'team_reward'],
        ];

        foreach ($commissionLevels as $commission) {
            CommissionLevel::updateOrCreate(
                ['level' => $commission['level'], 'type' => $commission['type']],
                ['percentage' => $commission['percentage']]
            );
        }
    }
}
