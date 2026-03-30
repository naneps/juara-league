<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Try to get the test user, or create one if not exists
        $user = User::where('email', 'test@example.com')->first() 
                ?? User::factory()->create(['email' => 'test@example.com', 'name' => 'Admin Juara']);

        // Create 10 tournaments for this user
        Tournament::factory()->count(10)->create([
            'user_id' => $user->id,
        ]);

        // Create 10 more tournaments for random users
        Tournament::factory()->count(10)->create();
    }
}
