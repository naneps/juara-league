<?php

namespace Database\Factories;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'free']),
            'notes' => $this->faker->sentence(),
            'created_at' => now()->subDays($this->faker->numberBetween(1, 15)),
        ];
    }
}
