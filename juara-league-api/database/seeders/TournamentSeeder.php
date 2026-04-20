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
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@juara-league.id')->first();
        $sports = Sport::all();
        $players = User::where('id', '!=', $organizer->id)->get();

        $this->command->info('🏆 Seeding realistic tournaments...');

        // SCENARIO 1: Draft Tournaments (3)
        Tournament::factory()->count(3)->create([
            'user_id' => $organizer->id,
            'status' => 'draft',
            'sport_id' => $sports->random()->id,
        ]);

        // SCENARIO 2: Registration Open (8)
        $registrations = Tournament::factory()->count(8)->create([
            'user_id' => $organizer->id,
            'status' => 'registration',
            'sport_id' => $sports->random()->id,
        ]);

        foreach ($registrations as $tournament) {
            $this->seedParticipants($tournament, $players);
        }

        // SCENARIO 3: Ongoing (5)
        $ongoing = Tournament::factory()->count(5)->create([
            'user_id' => $organizer->id,
            'status' => 'ongoing',
            'sport_id' => $sports->random()->id,
        ]);

        foreach ($ongoing as $tournament) {
            $participants = $this->seedParticipants($tournament, $players, true);
            $this->seedStagesAndMatches($tournament, $participants, 'ongoing');
        }

        // SCENARIO 4: Completed (4)
        $completed = Tournament::factory()->count(4)->create([
            'user_id' => $organizer->id,
            'status' => 'completed',
            'sport_id' => $sports->random()->id,
        ]);

        foreach ($completed as $tournament) {
            $participants = $this->seedParticipants($tournament, $players, true);
            $this->seedStagesAndMatches($tournament, $participants, 'completed');
        }

        // SCENARIO 5: Other People's Tournaments (10)
        Tournament::factory()->count(10)->create();
    }

    private function seedParticipants($tournament, $players, $mostlyApproved = false): \Illuminate\Support\Collection
    {
        $targetCount = $tournament->max_participants;
        $shuffledPlayers = $players->shuffle()->take($targetCount);
        $participants = collect();

        foreach ($shuffledPlayers as $player) {
            $status = $mostlyApproved ? 'approved' : collect(['pending', 'approved', 'approved', 'rejected'])->random();
            
            if ($tournament->participant_type === 'team') {
                $team = Team::factory()->create(['captain_id' => $player->id]);
                $participant = Participant::factory()->create([
                    'tournament_id' => $tournament->id,
                    'user_id' => $player->id,
                    'team_id' => $team->id,
                    'status' => $status,
                ]);
            } else {
                $participant = Participant::factory()->create([
                    'tournament_id' => $tournament->id,
                    'user_id' => $player->id,
                    'status' => $status,
                ]);
            }
            
            if ($status === 'approved') {
                $participants->push($participant);
            }
        }
        
        return $participants;
    }

    private function seedStagesAndMatches($tournament, $participants, $status): void
    {
        if ($participants->count() < 2) return;

        $stage = \App\Models\Stage::factory()->create([
            'tournament_id' => $tournament->id,
            'name' => 'Main Bracket',
            'type' => $tournament->bracket_type,
            'status' => $status === 'completed' ? 'completed' : 'ongoing',
        ]);

        $approvedParticipants = $participants->values();
        $matchLimit = min(16, floor($approvedParticipants->count() / 2));

        for ($i = 0; $i < $matchLimit; $i++) {
            $p1 = $approvedParticipants[$i * 2];
            $p2 = $approvedParticipants[$i * 2 + 1];

            $matchStatus = $status === 'completed' ? 'completed' : collect(['pending', 'ongoing', 'completed'])->random();
            
            $matchData = [
                'stage_id' => $stage->id,
                'round' => 1,
                'match_number' => $i + 1,
                'participant_1_id' => $p1->id,
                'participant_2_id' => $p2->id,
                'status' => $matchStatus,
                'scheduled_at' => now()->subDays(rand(1, 5)),
                'bracket_side' => 'winners',
            ];

            if ($matchStatus === 'completed') {
                $winner = rand(0, 1) === 0 ? $p1 : $p2;
                $matchData['winner_id'] = $winner->id;
                $matchData['completed_at'] = now()->subDays(rand(1, 2));
                $matchData['scores'] = [
                    'p1' => [rand(10, 16), rand(10, 16)],
                    'p2' => [rand(5, 12), rand(5, 12)],
                ];
            }

            \App\Models\TournamentMatch::create($matchData);
        }
    }
}
