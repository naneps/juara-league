<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\User;
use App\Models\Sport;
use App\Models\Participant;
use App\Models\Team;
use App\Models\Stage;
use App\Services\StageService;
use App\Services\MatchService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GroupStageSeeder extends Seeder
{
    protected StageService $stageService;
    protected MatchService $matchService;

    public function __construct(StageService $stageService, MatchService $matchService)
    {
        $this->stageService = $stageService;
        $this->matchService = $matchService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@juara-league.id')->first();
        if (!$organizer) {
            $organizer = User::factory()->create([
                'name' => 'Tournament Organizer',
                'email' => 'organizer@juara-league.id',
                'password' => bcrypt('password'),
            ]);
            $organizer->assignRole('organizer');
        }

        $players = User::where('email', '!=', 'organizer@juara-league.id')->get();
        if ($players->count() < 16) {
            $players = User::factory()->count(20)->create();
        }

        $this->command->info('🏆 Seeding Group Stage (Multi-Group Round Robin) Tournament...');

        $sport = Sport::where('name', 'Sepak Bola')->first() ?: Sport::first();
        
        $t = Tournament::create([
            'user_id' => $organizer->id,
            'sport_id' => $sport->id,
            'title' => 'Juara League Champions Cup 2026',
            'slug' => 'champions-cup-' . Str::random(4),
            'description' => 'Turnamen bergengsi antar komunitas dengan format Group Stage followed by Playoffs.',
            'category' => 'Sport',
            'status' => 'ongoing',
            'mode' => 'open',
            'bracket_type' => 'round_robin', // Display preference
            'participant_type' => 'team',
            'team_size' => 11,
            'max_participants' => 16,
            'venue' => 'Gelora Bung Karno, Jakarta',
            'prize_pool' => 250000000,
            'start_at' => now()->subDays(5),
            'registration_start_at' => now()->subDays(30),
            'registration_end_at' => now()->subDays(10),
        ]);

        $this->command->info("    - Created Tournament: {$t->title}");

        // Create 16 Teams and register them
        $teamNames = [
            'Garuda Warriors', 'Elang Hitam FC', 'Macan Kemayoran', 'Singo Edan',
            'Sultan United', 'Borneo Pride', 'Celebes Stars', 'Papua Glory',
            'Sumatra Kings', 'Bali Breeze', 'Lombok Wanderers', 'Bangka Sailors',
            'Dayak Hunters', 'Madura Bulls', 'Baduy Guardians', 'Ternate Pearls'
        ];

        foreach ($teamNames as $index => $name) {
            $captain = $players[$index];
            $team = Team::create([
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(4),
                'captain_id' => $captain->id,
                'status' => 'approved',
            ]);

            Participant::create([
                'tournament_id' => $t->id,
                'user_id' => $captain->id,
                'team_id' => $team->id,
                'status' => 'approved',
            ]);
        }

        // Stage 1: Group Stage (4 Groups of 4)
        $s1 = Stage::create([
            'tournament_id' => $t->id,
            'name' => 'Group Stage',
            'type' => 'round_robin',
            'status' => 'pending',
            'order' => 1,
            'groups_count' => 4,
            'participants_per_group' => 4,
            'bo_format' => 'bo1',
            'participants_advance' => 8, // Top 2 from each group
        ]);

        // Start Stage & Generate Matches
        $this->stageService->startStage($s1);

        // Auto Schedule
        $this->matchService->autoScheduleMatches($s1, [
            'start_at' => $t->start_at->setHour(14)->setMinute(0)->setSecond(0),
            'interval_minutes' => 120,
            'matches_per_day' => 4,
        ]);

        $this->command->info('    - Generated & Scheduled Group Stage (4 groups of 4)');

        // Simulate some results for Group A to show the standings
        $groupA = $s1->groups()->where('name', 'Group A')->first();
        if ($groupA) {
            $matches = $groupA->matches()->get();
            foreach ($matches->take(2) as $match) {
                $this->matchService->updateMatch($match, ['status' => 'ongoing']);
                
                $winnerId = $match->participant_1_id; // Let's make P1 win
                $this->matchService->inputGameResult($match, [
                    'game_number' => 1,
                    'winner_id' => $winnerId,
                    'score_p1' => 2,
                    'score_p2' => 0,
                ]);
            }
        }

        // Stage 2: Knockout Stage (Playoffs)
        Stage::create([
            'tournament_id' => $t->id,
            'name' => 'Playoffs',
            'type' => 'single_elim',
            'status' => 'pending',
            'order' => 2,
            'bo_format' => 'bo3',
        ]);

        $this->command->info('  ✓ Seeded Champions Cup with Group Stage!');
    }
}
