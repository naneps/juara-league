<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $prefixes = ['Garuda', 'Nusantara', 'Majapahit', 'Sriwijaya', 'Batavia', 'Borneo', 'Celebes', 'Papua', 'Andalas', 'Mataram', 'Singa', 'Harimau', 'Elang', 'Hiu', 'Naga'];
        $suffixes = ['Esports', 'United', 'Warriors', 'Kings', 'Legends', 'Elite', 'Squad', 'Force', 'Titans', 'Hunters', 'Gaming', 'Pro', 'X', 'Academy', 'Society'];
        
        $name = $this->faker->randomElement($prefixes) . ' ' . $this->faker->randomElement($suffixes) . ' ' . $this->faker->unique()->numberBetween(1, 9999);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(3),
            'description' => $this->faker->paragraph,
            'captain_id' => User::factory(),
            'logo_url' => 'https://api.dicebear.com/7.x/identicon/svg?seed=' . urlencode($name),
        ];
    }
}
