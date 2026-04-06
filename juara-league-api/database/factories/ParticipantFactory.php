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
            'status' => 'pending',
            'notes' => 'Looking forward to it!',
        ];
    }
}
