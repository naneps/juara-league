<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Sport;
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
        $sports = ['Valorant Champions', 'Super League', 'Winter Cup', 'National Open', 'Community Shield', 'Regional Qualifier'];
        $categories = ['Soccer', 'Basketball', 'Mobile Legends', 'Valorant', 'Badminton', 'Tennis'];
        
        $category = $this->faker->randomElement($categories);
        $title = $this->faker->randomElement($sports) . ' ' . $category . ' ' . now()->year;
        
        $regStart = now()->subDays($this->faker->numberBetween(0, 30));
        $regEnd = (clone $regStart)->addDays($this->faker->numberBetween(7, 21));
        $startAt = (clone $regEnd)->addDays($this->faker->numberBetween(3, 10));

        return [
            'user_id' => User::factory(),
            'sport_id' => Sport::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(5),
            'description' => $this->faker->paragraphs(3, true),
            'category' => $category,
            'status' => $this->faker->randomElement(['draft', 'registration', 'ongoing', 'completed']),
            'mode' => $this->faker->randomElement(['open', 'invite']),
            'participant_type' => $this->faker->randomElement(['individual', 'team']),
            'bracket_type' => $this->faker->randomElement(['single', 'double', 'round_robin']),
            'venue' => $this->faker->randomElement(['Online', 'Gelora Bung Karno', 'Istora Senayan', 'Community Center', 'Discord Server']),
            'banner_url' => 'https://picsum.photos/1200/400?random=' . $this->faker->unique()->numberBetween(1, 1000),
            'prize_pool' => $this->faker->randomElement([5000000, 10000000, 25000000, 50000000, 100000000]),
            'entry_fee' => $this->faker->randomElement([0, 25000, 50000, 100000, 250000]),
            'max_participants' => $this->faker->randomElement([8, 16, 32, 64]),
            'registration_start_at' => $regStart,
            'registration_end_at' => $regEnd,
            'start_at' => $startAt,
        ];
    }
}
