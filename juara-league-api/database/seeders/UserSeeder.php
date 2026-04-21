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
            'name' => 'Budi Santoso',
            'username' => 'budi_s',
            'email' => 'participant@juara-league.id',
            'password' => Hash::make('password'),
        ]);

        // 4. Specific Real-ish Users for Seeders
        $realUsers = [
            ['name' => 'Ahmad Fauzi', 'username' => 'ahmdfz'],
            ['name' => 'Siti Aminah', 'username' => 'sitiamn'],
            ['name' => 'Eko Prasetyo', 'username' => 'ekopra'],
            ['name' => 'Dewi Lestari', 'username' => 'dewilm'],
            ['name' => 'Rizky Pratama', 'username' => 'rizkyp'],
            ['name' => 'Lani Cahya', 'username' => 'lanic'],
            ['name' => 'Kevin Sanjay', 'username' => 'kevinsj'],
            ['name' => 'Marcus Gideon', 'username' => 'marcusg'],
            ['name' => 'Fajar Alfian', 'username' => 'fajara'],
            ['name' => 'Rian Ardianto', 'username' => 'riana'],
            ['name' => 'Jonathan Christi', 'username' => 'jchristi'],
            ['name' => 'Anthony Ginting', 'username' => 'aginting'],
            ['name' => 'Greysia Polii', 'username' => 'greysp'],
            ['name' => 'Apriyani Rahayu', 'username' => 'apriyani'],
            ['name' => 'Lemon Esport', 'username' => 'lemon'],
            ['name' => 'Albert Neils', 'username' => 'albert'],
            ['name' => 'Vyn Keren', 'username' => 'vynnn'],
            ['name' => 'Sanz King', 'username' => 'sanz'],
            ['name' => 'Butsss Kapten', 'username' => 'butss'],
            ['name' => 'Kiboy Roam', 'username' => 'kiboy'],
        ];

        foreach ($realUsers as $userData) {
            User::factory()->create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['username'] . '@example.com',
                'password' => Hash::make('password'),
            ]);
        }

        // 5. Random Users for Volume
        User::factory()->count(50)->create();
    }
}
