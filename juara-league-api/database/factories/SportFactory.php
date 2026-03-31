<?php

namespace Database\Factories;

use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sport>
 */
class SportFactory extends Factory
{
    protected $model = Sport::class;

    public function definition(): array
    {
        return [
            'name'     => $this->faker->unique()->word(),
            'type'     => $this->faker->randomElement(['sport', 'esport']),
            'icon_url' => $this->faker->imageUrl(),
            'is_active' => true,
        ];
    }
}
