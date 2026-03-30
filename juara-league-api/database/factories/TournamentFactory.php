<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(['Football', 'Esports', 'Badminton']),
            'status' => $this->faker->randomElement(['draft', 'open', 'ongoing', 'finished']),
            'mode' => $this->faker->randomElement(['online', 'offline']),
            'bracket_type' => $this->faker->randomElement(['single', 'double', 'round_robin', 'swiss']),
            'venue' => $this->faker->address,
            'banner_url' => $this->faker->imageUrl(),
            'prize_pool' => $this->faker->numberBetween(1000000, 50000000),
            'entry_fee' => $this->faker->numberBetween(10000, 500000),
            'max_participants' => $this->faker->numberBetween(8, 32),
            'registration_start_at' => now(),
            'registration_end_at' => now()->addDays(7),
            'start_at' => now()->addDays(14),
        ];
    }
}
