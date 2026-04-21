<?php

namespace Database\Seeders;

use App\Models\Tournament;
use App\Models\User;
use App\Models\Sport;
use App\Models\Participant;
use App\Models\Team;
use App\Models\Stage;
use App\Models\Group;
use App\Models\TournamentMatch;
use App\Services\StageService;
use App\Services\MatchService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TournamentSeeder extends Seeder
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
        $players = User::where('email', '!=', 'organizer@juara-league.id')->get();
        
        $this->command->info('🏆 Seeding Realistic Tournaments with Matches & Scheduling...');

        // 1. SCENARIO: MPL ID Season 13 (Multi-Stage: Group -> Playoffs)
        $this->seedMPL($organizer, $players);

        // 2. SCENARIO: Jakarta Badminton Open (Single Elimination)
        $this->seedBadminton($organizer, $players);

        // 3. SCENARIO: Nusantara Football League (League/Round Robin)
        $this->seedFootball($organizer, $players);

        // 4. SCENARIO: Valorant Community Cup (Double Elimination)
        $this->seedValorant($organizer, $players);

        // 5. SCENARIO: Finished Bracket Examples
        $this->seedFinishedTournaments($organizer, $players);

        // 6. Some Draft & Random Tournaments
        $this->seedRandom($organizer, $players);
    }

    private function seedMPL($organizer, $players): void
    {
        $sport = Sport::where('name', 'Mobile Legends: Bang Bang')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id' => $organizer->id,
            'sport_id' => $sport->id,
            'title' => 'MLBB Pro League Season 13',
            'slug' => 'mpl-id-s13-' . Str::random(4),
            'description' => 'Turnamen kasta tertinggi MLBB di Indonesia dengan total hadiah 5 Miliar Rupiah.',
            'category' => 'Esport',
            'status' => 'ongoing',
            'mode' => 'invite',
            'bracket_type' => 'double',
            'participant_type' => 'team',
            'team_size' => 5,
            'venue' => 'MPL Arena, Jakarta',
            'prize_pool' => 5000000000,
            'max_participants' => 8,
            'start_at' => now()->subDays(10),
            'registration_start_at' => now()->subDays(30),
            'registration_end_at' => now()->subDays(15),
        ]);

        $this->command->info("    - Created Tournament: {$t->title}");

        // Create 8 Teams
        $teamNames = ['ONIC Esport', 'RRQ Hoshi', 'EVOS Legends', 'Alter Ego', 'Bigetron Alpha', 'Aura Fire', 'Geek Fam', 'Rebellion Sinai'];
        
        foreach ($teamNames as $index => $name) {
            $captain = $players[$index];
            $team = Team::create([
                'name' => $name,
                'slug' => Str::slug($name),
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

        // Stage 1: Regular Season (Round Robin)
        $s1 = Stage::create([
            'tournament_id' => $t->id,
            'name' => 'Regular Season',
            'type' => 'round_robin',
            'status' => 'pending', 
            'order' => 1,
            'groups_count' => 1,
            'participants_per_group' => 8,
        ]);

        // Generate Matches
        $this->stageService->startStage($s1);
        
        // Auto Schedule
        $this->matchService->autoScheduleMatches($s1, [
            'start_at' => $t->start_at->setHour(10)->setMinute(0)->setSecond(0),
            'interval_minutes' => 90,
            'matches_per_day' => 4,
        ]);
        
        $this->command->info('    - Generated & Scheduled Regular Season matches');

        // Stage 2: Playoffs (Double Elimination)
        Stage::create([
            'tournament_id' => $t->id,
            'name' => 'Playoffs',
            'type' => 'double_elim',
            'status' => 'pending',
            'order' => 2,
        ]);
        
        $this->command->info('  ✓ Seeded MPL ID (Multi-Stage)');
    }

    private function seedBadminton($organizer, $players): void
    {
        $sport = Sport::where('name', 'Badminton')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id' => $organizer->id,
            'sport_id' => $sport->id,
            'title' => 'Jakarta Badminton Open 2026',
            'slug' => 'jkt-badminton-open-' . Str::random(4),
            'description' => 'Turnamen terbuka untuk kategori Tunggal Putra.',
            'category' => 'Sport',
            'status' => 'ongoing',
            'mode' => 'open',
            'bracket_type' => 'single',
            'participant_type' => 'individual',
            'venue' => 'Istora Senayan',
            'max_participants' => 16,
            'entry_fee' => 50000,
            'start_at' => now()->addDays(20),
            'registration_start_at' => now()->subDays(5),
            'registration_end_at' => now()->addDays(15),
        ]);

        $this->command->info("    - Created Tournament: {$t->title}");

        $shuffled = $players->shuffle()->take(16);
        foreach ($shuffled as $player) {
            Participant::create([
                'tournament_id' => $t->id,
                'user_id' => $player->id,
                'status' => 'approved',
            ]);
        }

        $s1 = Stage::create([
            'tournament_id' => $t->id,
            'name' => 'Main Bracket',
            'type' => 'single_elim',
            'status' => 'pending',
            'order' => 1,
        ]);

        // Generate Matches
        $this->stageService->startStage($s1);
        
        // Auto Schedule
        $this->matchService->autoScheduleMatches($s1, [
            'start_at' => $t->start_at->setHour(8)->setMinute(0)->setSecond(0),
            'interval_minutes' => 45,
            'matches_per_day' => 8,
        ]);
        
        $this->command->info('    - Generated & Scheduled Badminton bracket');

        $this->command->info('  ✓ Seeded Jakarta Badminton Open (Single Elim)');
    }

    private function seedFootball($organizer, $players): void
    {
        $sport = Sport::where('name', 'Sepak Bola')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id' => $organizer->id,
            'sport_id' => $sport->id,
            'title' => 'Nusantara Football League',
            'slug' => 'nusantara-league-' . Str::random(4),
            'description' => 'Liga komunitas sepak bola antar kota.',
            'category' => 'Sport',
            'status' => 'ongoing',
            'mode' => 'open',
            'bracket_type' => 'round_robin',
            'participant_type' => 'team',
            'team_size' => 11,
            'max_participants' => 10,
            'start_at' => now()->subDays(15),
        ]);

        $this->command->info("    - Created Tournament: {$t->title}");

        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Makassar', 'Semarang', 'Palembang', 'Bali', 'Solo', 'Yogyakarta'];
        foreach ($cities as $index => $city) {
            $name = $city . ' United FC';
            $team = Team::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'captain_id' => $players[$index + 20]->id,
                'status' => 'approved',
            ]);

            Participant::create([
                'tournament_id' => $t->id,
                'user_id' => $team->captain_id,
                'team_id' => $team->id,
                'status' => 'approved',
            ]);
        }

        $s1 = Stage::create([
            'tournament_id' => $t->id,
            'name' => 'Regular Season',
            'type' => 'round_robin',
            'status' => 'pending',
            'order' => 1,
            'groups_count' => 1,
            'participants_per_group' => 10,
        ]);

        // Generate Matches
        $this->stageService->startStage($s1);
        
        // Auto Schedule
        $this->matchService->autoScheduleMatches($s1, [
            'start_at' => $t->start_at->setHour(16)->setMinute(0)->setSecond(0),
            'interval_minutes' => 120, // 2 hours per match
            'matches_per_day' => 2,
        ]);
        
        $this->command->info('    - Generated & Scheduled Football League');

        $this->command->info('  ✓ Seeded Nusantara Football League (League)');
    }

    private function seedValorant($organizer, $players): void
    {
        $sport = Sport::where('name', 'VALORANT')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id' => $organizer->id,
            'sport_id' => $sport->id,
            'title' => 'Valorant Community Cup',
            'slug' => 'valorant-comm-cup-' . Str::random(4),
            'status' => 'ongoing',
            'mode' => 'open',
            'bracket_type' => 'double',
            'participant_type' => 'team',
            'team_size' => 5,
            'max_participants' => 8,
            'start_at' => now()->subDays(20),
        ]);

        $this->command->info("    - Created Tournament: {$t->title}");

        $teamNames = ['Radiant Knights', 'Jett Main Team', 'Bind Campers', 'Split Masters', 'Icebox Kings', 'Ascent Angels', 'Haven Heroes', 'Lotus Legends'];
        foreach ($teamNames as $index => $name) {
            $team = Team::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'captain_id' => $players[$index + 35]->id,
                'status' => 'approved',
            ]);

            Participant::create([
                'tournament_id' => $t->id,
                'user_id' => $team->captain_id,
                'team_id' => $team->id,
                'status' => 'approved',
            ]);
        }

        $s1 = Stage::create([
            'tournament_id' => $t->id,
            'name' => 'Playoffs',
            'type' => 'double_elim',
            'status' => 'pending',
            'order' => 1,
        ]);

        // Generate Matches
        $this->stageService->startStage($s1);
        
        // Auto Schedule
        $this->matchService->autoScheduleMatches($s1, [
            'start_at' => $t->start_at->setHour(13)->setMinute(0)->setSecond(0),
            'interval_minutes' => 60,
            'matches_per_day' => 6,
        ]);
        
        $this->command->info('    - Generated & Scheduled Valorant bracket');

        $this->command->info('  ✓ Seeded Valorant Community Cup (Double Elim)');
    }

    private function seedFinishedTournaments($organizer, $players): void
    {
        $sport = Sport::first();
        if (!$sport) return;

        // 1. Finished Single Elimination
        $t1 = Tournament::create([
            'user_id' => $organizer->id,
            'sport_id' => $sport->id,
            'title' => 'Juara League: The Last Stand (Single Elim)',
            'slug' => 'jl-finished-single-' . Str::random(4),
            'status' => 'ongoing',
            'mode' => 'open',
            'bracket_type' => 'single',
            'participant_type' => 'individual',
            'max_participants' => 8,
            'start_at' => now()->subDays(30),
        ]);

        $this->command->info("    - Created Finished Tournament: {$t1->title}");

        $shuffled1 = $players->shuffle()->take(8);
        foreach ($shuffled1 as $player) {
            Participant::create(['tournament_id' => $t1->id, 'user_id' => $player->id, 'status' => 'approved']);
        }

        $s1 = Stage::create(['tournament_id' => $t1->id, 'name' => 'Main Bracket', 'type' => 'single_elim', 'status' => 'pending', 'order' => 1, 'bo_format' => 'bo1']);
        $this->stageService->startStage($s1);
        $this->matchService->autoScheduleMatches($s1, ['start_at' => $t1->start_at->copy()]);
        $this->simulateCompletedMatches($s1);
        $t1->update(['status' => 'completed']);

        // 2. Finished Double Elimination
        $t2 = Tournament::create([
            'user_id' => $organizer->id,
            'sport_id' => $sport->id,
            'title' => 'Juara League: Rebirth (Double Elim)',
            'slug' => 'jl-finished-double-' . Str::random(4),
            'status' => 'ongoing',
            'mode' => 'open',
            'bracket_type' => 'double',
            'participant_type' => 'individual',
            'max_participants' => 8,
            'start_at' => now()->subDays(30),
        ]);

        $this->command->info("    - Created Finished Tournament: {$t2->title}");

        $shuffled2 = $players->shuffle()->take(8);
        foreach ($shuffled2 as $player) {
            Participant::create(['tournament_id' => $t2->id, 'user_id' => $player->id, 'status' => 'approved']);
        }

        $s2 = Stage::create(['tournament_id' => $t2->id, 'name' => 'Main Event', 'type' => 'double_elim', 'status' => 'pending', 'order' => 1, 'bo_format' => 'bo1']);
        $this->stageService->startStage($s2);
        $this->matchService->autoScheduleMatches($s2, ['start_at' => $t2->start_at->copy()]);
        $this->simulateCompletedMatches($s2);
        $t2->update(['status' => 'completed']);

        $this->command->info('  ✓ Seeded Finished Tournaments (Single & Double Elim)');
    }

    private function simulateCompletedMatches(Stage $stage): void
    {
        $whileSafe = 50; 
        while ($whileSafe > 0) {
            // Find matches that are ready to be played (upcoming and have both participants)
            $playableMatches = $stage->matches()
                ->where('status', 'upcoming')
                ->whereNotNull('participant_1_id')
                ->whereNotNull('participant_2_id')
                ->get();

            if ($playableMatches->isEmpty()) {
                break; // all matches resolved
            }

            foreach ($playableMatches as $match) {
                // start match
                $this->matchService->updateMatch($match, ['status' => 'ongoing']);
                
                // randomly pick winner logic
                $winnerId = rand(0, 1) === 0 ? $match->participant_1_id : $match->participant_2_id;
                
                $this->matchService->inputGameResult($match, [
                    'game_number' => 1,
                    'winner_id' => $winnerId,
                    'score_p1' => $winnerId === $match->participant_1_id ? 13 : rand(1,10),
                    'score_p2' => $winnerId === $match->participant_2_id ? 13 : rand(1,10),
                ]);
            }
            $whileSafe--;
        }
    }

    private function seedRandom($organizer, $players): void
    {
        $sports = Sport::all();
        
        Tournament::factory()->count(2)->create([
            'user_id' => $organizer->id,
            'status' => 'draft',
            'sport_id' => $sports->random()->id,
        ]);

        Tournament::factory()->count(5)->create([
            'sport_id' => $sports->random()->id,
        ]);
        
        $this->command->info('  ✓ Seeded Random & Draft Tournaments');
    }
}
