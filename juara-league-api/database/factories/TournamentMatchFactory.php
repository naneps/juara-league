<?php

namespace Database\Factories;

use App\Models\TournamentMatch;
use App\Models\Stage;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TournamentMatchFactory extends Factory
{
    protected $model = TournamentMatch::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'round' => 1,
            'match_number' => $this->faker->numberBetween(1, 100),
            'status' => $this->faker->randomElement(['pending', 'ongoing', 'completed']),
            'scheduled_at' => now()->addDays($this->faker->numberBetween(1, 10)),
            'scores' => null,
            'bracket_side' => $this->faker->randomElement(['winners', 'losers']),
        ];
    }
    
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
            'scores' => [
                'p1' => [rand(0, 3), rand(0, 3), rand(0, 3)],
                'p2' => [rand(0, 3), rand(0, 3), rand(0, 3)],
            ],
        ]);
    }
}
