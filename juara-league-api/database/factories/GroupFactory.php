<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'name' => 'Group ' . $this->faker->unique()->lexify('?'),
            'order' => $this->faker->numberBetween(1, 4),
        ];
    }
}
