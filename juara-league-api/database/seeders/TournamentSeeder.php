<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\User;
use App\Models\Sport;
use App\Models\Participant;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@juara-league.id')->first();
        $sports = Sport::all();
        $players = User::where('id', '!=', $organizer->id)->get();

        // SCENARIO 1: Draft Tournaments (2)
        Tournament::factory()->count(2)->create([
            'user_id' => $organizer->id,
            'status' => 'draft',
            'sport_id' => $sports->random()->id,
        ]);

        // SCENARIO 2: Registration Open (5)
        $registrations = Tournament::factory()->count(5)->create([
            'user_id' => $organizer->id,
            'status' => 'registration',
            'sport_id' => $sports->random()->id,
        ]);

        foreach ($registrations as $tournament) {
            $this->seedParticipants($tournament, $players);
        }

        // SCENARIO 3: Ongoing (3)
        $ongoing = Tournament::factory()->count(3)->create([
            'user_id' => $organizer->id,
            'status' => 'ongoing',
            'sport_id' => $sports->random()->id,
        ]);

        foreach ($ongoing as $tournament) {
            $this->seedParticipants($tournament, $players, true); // More likely to be approved
        }

        // SCENARIO 4: Completed (2)
        $completed = Tournament::factory()->count(2)->create([
            'user_id' => $organizer->id,
            'status' => 'completed',
            'sport_id' => $sports->random()->id,
        ]);

        foreach ($completed as $tournament) {
            $this->seedParticipants($tournament, $players, true);
        }

        // SCENARIO 5: Other People's Tournaments (5)
        Tournament::factory()->count(5)->create();
    }

    private function seedParticipants($tournament, $players, $mostlyApproved = false): void
    {
        $count = rand(4, 12);
        $shuffledPlayers = $players->shuffle()->take($count);

        foreach ($shuffledPlayers as $player) {
            $status = $mostlyApproved ? 'approved' : collect(['pending', 'approved', 'rejected'])->random();
            
            if ($tournament->participant_type === 'team') {
                $team = Team::factory()->create(['captain_id' => $player->id]);
                Participant::factory()->create([
                    'tournament_id' => $tournament->id,
                    'user_id' => $player->id,
                    'team_id' => $team->id,
                    'status' => $status,
                ]);
            } else {
                Participant::factory()->create([
                    'tournament_id' => $tournament->id,
                    'user_id' => $player->id,
                    'status' => $status,
                ]);
            }
        }
    }
}
