<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\ReferralSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // $this->call(ReferralSeeder::class);

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            SettingsAndCommissionsSeeder::class,
           InvestmentPlanSeeder::class,
          //  ReferralSeeder::class,
        ]);
        User::updateOrCreate(['email' => 'admin@nexora.uk.com'], [
            'name' => 'Nexora Uk',
            'username' => 'admin',
            'password' => bcrypt('Nexora.uk@100%'),
            'is_admin' => true,
            'email_verified_at' => now()
        ]);
    }
}
