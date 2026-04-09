<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Admin Account
        User::factory()->create([
            'name' => 'Super Admin Juara',
            'username' => 'admin',
            'email' => 'admin@juara-league.id',
            'password' => Hash::make('password'),
        ]);

        // 2. Organizer Account
        User::factory()->create([
            'name' => 'Penyelenggara Pro',
            'username' => 'organizer',
            'email' => 'organizer@juara-league.id',
            'password' => Hash::make('password'),
        ]);

        // 3. Participant Account
        User::factory()->create([
            'name' => 'Pemain Teladan',
            'username' => 'pemain',
            'email' => 'participant@juara-league.id',
            'password' => Hash::make('password'),
        ]);

        // 4. Random Users
        User::factory()->count(20)->create();
    }
}
