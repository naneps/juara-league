<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\Sport;
use App\Models\Stage;
use App\Models\Tournament;
use App\Models\User;
use App\Services\MatchService;
use App\Services\StageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BOFormatSeeder extends Seeder
{
    protected StageService $stageService;
    protected MatchService $matchService;

    public function __construct(StageService $stageService, MatchService $matchService)
    {
        $this->stageService = $stageService;
        $this->matchService = $matchService;
    }

    public function run(): void
    {
        $organizer = User::where('email', 'organizer@juara-league.id')->first();
        if (!$organizer) {
            $organizer = User::factory()->create([
                'email' => 'organizer@juara-league.id',
                'name' => 'Tournament Organizer',
                'username' => 'organizer',
            ]);
        }
        
        $players = User::where('email', '!=', 'organizer@juara-league.id')->get();
        if ($players->count() < 16) {
             $players = User::factory()->count(16)->create();
        }

        $this->command->info('🎮 Seeding BO Format Variety Tournaments...');

        $formats = [
            ['name' => 'BO1 - Single Game', 'win_condition' => 1, 'label' => 'bo1'],
            ['name' => 'BO3 - Best of 3', 'win_condition' => 2, 'label' => 'bo3'],
            ['name' => 'BO5 - Pro Finals', 'win_condition' => 3, 'label' => 'bo5'],
            ['name' => 'BO7 - Epic Showdown', 'win_condition' => 4, 'label' => 'bo7'],
        ];

        $sport = Sport::where('name', 'Mobile Legends: Bang Bang')->first() ?? Sport::first();

        foreach ($formats as $index => $f) {
            $t = Tournament::create([
                'user_id'          => $organizer->id,
                'sport_id'         => $sport->id,
                'title'            => 'Showcase ' . $f['name'],
                'slug'             => Str::slug($f['name']) . '-' . Str::random(4),
                'description'      => "Menampilkan format " . strtoupper($f['label']) . ". Win Condition: Race to " . $f['win_condition'] . " wins.",
                'category'         => 'Exhibition',
                'status'           => 'ongoing',
                'mode'             => 'open',
                'bracket_type'     => ($f['win_condition'] > 2) ? 'double' : 'single',
                'participant_type' => 'individual',
                'max_participants' => 8,
                'start_at'         => now(),
                'registration_start_at' => now()->subDays(1),
                'registration_end_at' => now()->addDays(1),
            ]);

            // Add 8 participants
            foreach ($players->shuffle()->take(8) as $player) {
                Participant::create([
                    'tournament_id' => $t->id,
                    'user_id'       => $player->id,
                    'status'        => 'approved',
                    'payment_status' => 'paid',
                ]);
            }

            $stage = Stage::create([
                'tournament_id' => $t->id,
                'name'          => 'Main Event',
                'type'          => ($f['win_condition'] > 2) ? 'double_elim' : 'single_elim',
                'status'        => 'pending',
                'order'         => 1,
                'participants_advance' => 1,
                'settings'      => [
                    'match_format'   => 'best_of',
                    'win_condition'  => $f['win_condition'],
                    'scoring_method' => 'score_based',
                    'advance_count'  => 1,
                    'rules'          => [
                        'allow_draw' => false, 
                        'extra_time' => false, 
                        'penalties' => false
                    ],
                ],
            ]);

            $this->stageService->startStage($stage);
            $this->matchService->autoScheduleMatches($stage, [
                'start_at' => now()->addHours($index),
                'interval_minutes' => 60,
            ]);

            $this->command->info("  ✓ Created {$f['name']} ({$f['label']})");
        }
    }
}
