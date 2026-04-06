<?php

namespace Database\Factories;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

class StageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tournament_id' => Tournament::factory(),
            'name' => 'Group Stage',
            'type' => 'round_robin',
            'order' => 1,
            'settings' => ['bo' => 1],
        ];
    }
}
