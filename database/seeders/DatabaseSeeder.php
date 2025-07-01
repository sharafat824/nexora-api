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
        User::updateOrCreate(['email' => 'admin@test.com'],[
            'name' => 'Admin',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }
}
