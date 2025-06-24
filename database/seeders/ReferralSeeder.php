<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReferralSeeder extends Seeder
{
    public function run(): void
    {
        $users = [];

        // Level 0
        $ali = $this->createUser('Ali');
        $users['Ali'] = $ali;

        // Level 1
        $ahmad = $this->createUser('Ahmad', $ali->id);
        $ahsan = $this->createUser('Ahsan', $ali->id);
        $irfan = $this->createUser('Irfan', $ali->id);
        $users += compact('ahmad', 'ahsan', 'irfan');

        // Level 2
        $bilal = $this->createUser('Bilal', $ahmad->id);
        $faraz = $this->createUser('Faraz', $ahmad->id);
        $ghani = $this->createUser('Ghani', $ahsan->id);
        $jamal = $this->createUser('Jamal', $irfan->id);
        $users += compact('bilal', 'faraz', 'ghani', 'jamal');

        // Level 3
        $danish = $this->createUser('Danish', $bilal->id);
        $hammad = $this->createUser('Hammad', $ghani->id);
        $kamran = $this->createUser('Kamran', $jamal->id);
        $users += compact('danish', 'hammad', 'kamran');

        // Level 4
        $ehsan = $this->createUser('Ehsan', $danish->id);
        $users['Ehsan'] = $ehsan;

        // Show tree roots and counts
        echo "🌳 Referral structure created:\n";
        foreach ($users as $name => $user) {
            echo "- {$name} (ID: {$user->id}, referred_by: {$user->referred_by})\n";
        }
    }

    private function createUser(string $name, $referredBy = null)
    {
        return User::create([
            'name' => $name,
            'username' => strtolower($name),
            'email' => strtolower($name) . '@example.com',
            'password' => bcrypt('password'),
            'referral_code' => strtoupper(Str::random(8)),
            'referred_by' => $referredBy
        ]);
    }
}
