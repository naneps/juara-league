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
        $tournamentNames = [
            'Piala Presiden Esports', 'Indonesia Open', 'National Championship', 'Regional Qualifier', 
            'Community Shield', 'Super League', 'Winter Cup', 'Summer Invitational', 
            'Liga Mahasiswa (LIMA)', 'Turnamen Antar Media', 'Piala Menpora', 'King of Kings Arena'
        ];
        
        $categories = ['Mobile Legends', 'VALORANT', 'Free Fire', 'PUBG Mobile', 'Badminton', 'Futsal', 'Basketball'];
        
        $name = $this->faker->randomElement($tournamentNames);
        $category = $this->faker->randomElement($categories);
        $title = $name . ' ' . $category . ' ' . now()->year;
        
        $regStart = now()->subDays($this->faker->numberBetween(1, 45));
        $regEnd = (clone $regStart)->addDays($this->faker->numberBetween(10, 25));
        $startAt = (clone $regEnd)->addDays($this->faker->numberBetween(3, 15));

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
            'venue' => $this->faker->randomElement([
                'Istora Senayan, Jakarta', 
                'Gelora Bung Karno, Jakarta', 
                'Mall Taman Anggrek, Jakarta', 
                'Online (Official Discord)', 
                'Bali United Studio', 
                'Tunjungan Plaza, Surabaya',
                'Cihampelas Walk, Bandung',
                'Stadion Manahan, Solo'
            ]),
            'banner_url' => 'https://picsum.photos/1200/400?random=' . $this->faker->unique()->numberBetween(1, 1000),
            'prize_pool' => $this->faker->randomElement([10000000, 25000000, 50000000, 100000000, 250000000]),
            'entry_fee' => $this->faker->randomElement([0, 50000, 100000, 250000]),
            'max_participants' => $this->faker->randomElement([16, 32, 64]),
            'registration_start_at' => $regStart,
            'registration_end_at' => $regEnd,
            'start_at' => $startAt,
        ];
    }
}
