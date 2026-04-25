<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\MatchParticipant;
use App\Models\Participant;
use App\Models\Sport;
use App\Models\Stage;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Services\MatchService;
use App\Services\StageService;
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

    public function run(): void
    {
        $organizer = User::where('email', 'organizer@juara-league.id')->first();
        $players   = User::where('email', '!=', 'organizer@juara-league.id')->get();

        $this->command->info('🏆 Seeding Comprehensive Tournaments (All Formats & Scoring Methods)...');

        // 1. Esport – Best-of + Score Based  (MLBB, Single Elim)
        $this->seedEsportBO($organizer, $players);

        // 2. Sport – Single Game + Result Based  (Badminton, Single Elim)
        $this->seedBadminton($organizer, $players);

        // 3. Sport – Leg (Home & Away) + Result Based  (Football, Round Robin)
        $this->seedFootball($organizer, $players);

        // 4. Esport – Best-of + Score Based  (Valorant, Double Elim)
        $this->seedValorant($organizer, $players);

        // 5. Esport – Best-of + Score Based  (DOTA 2, Swiss)
        $this->seedSwiss($organizer, $players);

        // 6. Sport – Single Game + Point Based  (Futsal, Round Robin multi-group)
        $this->seedFutsalMultiGroup($organizer, $players);

        // 7. Finished examples (single + double)
        $this->seedFinished($organizer, $players);

        // 8. Draft / random
        $this->seedRandom($organizer, $players);
    }

    // ─────────────────────────────────────────
    // 1. MLBB – Single Elim, Best-of-3, Score Based
    // ─────────────────────────────────────────
    private function seedEsportBO($organizer, $players): void
    {
        $sport = Sport::where('name', 'Mobile Legends: Bang Bang')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id'                => $organizer->id,
            'sport_id'               => $sport->id,
            'title'                  => 'MLBB Pro League Season 13',
            'slug'                   => 'mpl-id-s13-' . Str::random(4),
            'description'            => 'Turnamen MLBB kasta tertinggi. Format Best-of-3, Race to 2 wins.',
            'category'               => 'Esport',
            'status'                 => 'ongoing',
            'mode'                   => 'invite',
            'bracket_type'           => 'single',
            'participant_type'       => 'team',
            'team_size'              => 5,
            'venue'                  => 'MPL Arena, Jakarta',
            'prize_pool'             => 5000000000,
            'max_participants'       => 8,
            'start_at'               => now()->subDays(10),
            'registration_start_at'  => now()->subDays(30),
            'registration_end_at'    => now()->subDays(15),
        ]);

        $teamNames = ['ONIC Esport', 'RRQ Hoshi', 'EVOS Legends', 'Alter Ego', 'Bigetron Alpha', 'Aura Fire', 'Geek Fam', 'Rebellion Sinai'];
        $this->createTeamParticipants($t, $players, $teamNames, 0);

        $stage = Stage::create([
            'tournament_id' => $t->id,
            'name'          => 'Main Bracket',
            'type'          => 'single_elim',
            'status'        => 'pending',
            'order'         => 1,
            'participants_advance' => 1,
            'settings'      => [
                'match_format'   => 'best_of',
                'win_condition'  => 2,   // Race to 2 = BO3
                'scoring_method' => 'score_based',
                'advance_count'  => 1,
                'rules'          => ['allow_draw' => false, 'extra_time' => false, 'penalties' => false],
            ],
        ]);

        $this->stageService->startStage($stage);
        $this->matchService->autoScheduleMatches($stage, [
            'start_at'         => $t->start_at->setHour(10)->setMinute(0)->setSecond(0),
            'interval_minutes' => 90,
            'matches_per_day'  => 4,
        ]);

        $this->command->info('  ✓ MLBB Pro League – Single Elim, BO3, Score Based');
    }

    // ─────────────────────────────────────────
    // 2. BADMINTON – Single Elim, Single Game, Result Based
    // ─────────────────────────────────────────
    private function seedBadminton($organizer, $players): void
    {
        $sport = Sport::where('name', 'Badminton')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id'                => $organizer->id,
            'sport_id'               => $sport->id,
            'title'                  => 'Jakarta Badminton Open 2026',
            'slug'                   => 'jkt-badminton-' . Str::random(4),
            'description'            => 'Turnamen terbuka Tunggal Putra. Format Single Game, Result Based.',
            'category'               => 'Sport',
            'status'                 => 'ongoing',
            'mode'                   => 'open',
            'bracket_type'           => 'single',
            'participant_type'       => 'individual',
            'entry_fee'              => 50000,
            'max_participants'       => 16,
            'start_at'               => now()->addDays(20),
            'registration_start_at'  => now()->subDays(5),
            'registration_end_at'    => now()->addDays(15),
        ]);

        $this->createIndividualParticipants($t, $players->shuffle()->take(16));

        $stage = Stage::create([
            'tournament_id' => $t->id,
            'name'          => 'Main Bracket',
            'type'          => 'single_elim',
            'status'        => 'pending',
            'order'         => 1,
            'participants_advance' => 1,
            'settings'      => [
                'match_format'   => 'single_game',
                'win_condition'  => 1,
                'scoring_method' => 'result_based',
                'advance_count'  => 1,
                'rules'          => ['allow_draw' => false, 'extra_time' => true, 'penalties' => false],
            ],
        ]);

        $this->stageService->startStage($stage);
        $this->matchService->autoScheduleMatches($stage, [
            'start_at'         => $t->start_at->setHour(8),
            'interval_minutes' => 45,
            'matches_per_day'  => 8,
        ]);

        $this->command->info('  ✓ Jakarta Badminton Open – Single Elim, Single Game, Result Based');
    }

    // ─────────────────────────────────────────
    // 3. FOOTBALL – Round Robin, Home & Away (Leg), Result Based
    // ─────────────────────────────────────────
    private function seedFootball($organizer, $players): void
    {
        $sport = Sport::where('name', 'Sepak Bola')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id'          => $organizer->id,
            'sport_id'         => $sport->id,
            'title'            => 'Nusantara Football League',
            'slug'             => 'nusantara-league-' . Str::random(4),
            'description'      => 'Liga komunitas sepak bola. Format Home & Away, Result Based (3/1/0).',
            'category'         => 'Sport',
            'status'           => 'ongoing',
            'mode'             => 'open',
            'bracket_type'     => 'round_robin',
            'participant_type' => 'team',
            'team_size'        => 11,
            'max_participants' => 8,
            'start_at'         => now()->subDays(15),
        ]);

        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Makassar', 'Semarang', 'Bali', 'Solo'];
        $this->createTeamParticipants($t, $players, array_map(fn($c) => "$c United FC", $cities), 20);

        $stage = Stage::create([
            'tournament_id'        => $t->id,
            'name'                 => 'Liga Utama',
            'type'                 => 'round_robin',
            'status'               => 'pending',
            'order'                => 1,
            'groups_count'         => 1,
            'participants_per_group' => 8,
            'participants_advance' => 4,
            'settings'             => [
                'match_format'   => 'leg',
                'win_condition'  => 1,
                'scoring_method' => 'result_based',
                'advance_count'  => 4,
                'rules'          => ['allow_draw' => true, 'extra_time' => false, 'penalties' => false],
            ],
        ]);

        $this->stageService->startStage($stage);
        $this->matchService->autoScheduleMatches($stage, [
            'start_at'         => $t->start_at->setHour(16),
            'interval_minutes' => 120,
            'matches_per_day'  => 2,
        ]);

        $this->command->info('  ✓ Nusantara Football League – Round Robin, Leg, Result Based');
    }

    // ─────────────────────────────────────────
    // 4. VALORANT – Double Elim, Best-of-5, Score Based
    // ─────────────────────────────────────────
    private function seedValorant($organizer, $players): void
    {
        $sport = Sport::where('name', 'VALORANT')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id'          => $organizer->id,
            'sport_id'         => $sport->id,
            'title'            => 'Valorant Community Cup',
            'slug'             => 'valorant-cup-' . Str::random(4),
            'description'      => 'Double Elimination, Best-of-5 Grand Final, Score Based.',
            'category'         => 'Esport',
            'status'           => 'ongoing',
            'mode'             => 'open',
            'bracket_type'     => 'double',
            'participant_type' => 'team',
            'team_size'        => 5,
            'max_participants' => 8,
            'start_at'         => now()->subDays(7),
        ]);

        $teamNames = ['Radiant Knights', 'Jett Mains', 'Bind Campers', 'Split Masters', 'Icebox Kings', 'Ascent Angels', 'Haven Heroes', 'Lotus Legends'];
        $this->createTeamParticipants($t, $players, $teamNames, 35);

        $stage = Stage::create([
            'tournament_id' => $t->id,
            'name'          => 'Playoffs',
            'type'          => 'double_elim',
            'status'        => 'pending',
            'order'         => 1,
            'participants_advance' => 1,
            'settings'      => [
                'match_format'   => 'best_of',
                'win_condition'  => 3,   // Race to 3 = BO5
                'scoring_method' => 'score_based',
                'advance_count'  => 1,
                'rules'          => ['allow_draw' => false, 'extra_time' => false, 'penalties' => false],
            ],
        ]);

        $this->stageService->startStage($stage);
        $this->matchService->autoScheduleMatches($stage, [
            'start_at'         => $t->start_at->setHour(13),
            'interval_minutes' => 60,
            'matches_per_day'  => 6,
        ]);

        $this->command->info('  ✓ Valorant Community Cup – Double Elim, BO5, Score Based');
    }

    // ─────────────────────────────────────────
    // 5. DOTA 2 – Swiss, Best-of-3, Score Based
    // ─────────────────────────────────────────
    private function seedSwiss($organizer, $players): void
    {
        $sport = Sport::where('name', 'Dota 2')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id'          => $organizer->id,
            'sport_id'         => $sport->id,
            'title'            => 'Dota 2 Swiss Masters',
            'slug'             => 'dota2-swiss-' . Str::random(4),
            'description'      => 'Format Swiss System, Best-of-3, Score Based. Fairest pairing system.',
            'category'         => 'Esport',
            'status'           => 'ongoing',
            'mode'             => 'open',
            'bracket_type'     => 'swiss',
            'participant_type' => 'team',
            'team_size'        => 5,
            'prize_pool'       => 1000000000,
            'max_participants' => 8,
            'start_at'         => now()->subDays(5),
        ]);

        $teamNames = ['OG Clones', 'Evil Geniuses ID', 'Team Spirit ID', 'Tundra Esports ID', 'Secret Society', 'Liquid ID', 'Alliance ID', 'Virtus.pro ID'];
        $this->createTeamParticipants($t, $players, $teamNames, 44);

        $stage = Stage::create([
            'tournament_id' => $t->id,
            'name'          => 'Swiss Stage',
            'type'          => 'swiss',
            'status'        => 'pending',
            'order'         => 1,
            'participants_advance' => 4,
            'settings'      => [
                'match_format'   => 'best_of',
                'win_condition'  => 2,   // BO3
                'scoring_method' => 'score_based',
                'advance_count'  => 4,
                'rounds'         => 5,
                'rules'          => ['allow_draw' => false, 'extra_time' => false, 'penalties' => false],
            ],
        ]);

        $this->stageService->startStage($stage);
        $this->matchService->autoScheduleMatches($stage, [
            'start_at'         => $t->start_at->setHour(15),
            'interval_minutes' => 120,
            'matches_per_day'  => 4,
        ]);

        $this->command->info('  ✓ Dota 2 Swiss Masters – Swiss, BO3, Score Based');
    }

    // ─────────────────────────────────────────
    // 6. FUTSAL – Round Robin Multi-Group, Single Game, Point Based
    // ─────────────────────────────────────────
    private function seedFutsalMultiGroup($organizer, $players): void
    {
        $sport = Sport::where('name', 'Futsal')->first();
        if (!$sport) return;

        $t = Tournament::create([
            'user_id'          => $organizer->id,
            'sport_id'         => $sport->id,
            'title'            => 'Futsal Nusantara Cup 2026',
            'slug'             => 'futsal-nusantara-' . Str::random(4),
            'description'      => 'Grup + Knockout. Fase Grup: Single Game, Point Based (3/1/0).',
            'category'         => 'Sport',
            'status'           => 'ongoing',
            'mode'             => 'open',
            'bracket_type'     => 'round_robin',
            'participant_type' => 'team',
            'team_size'        => 7,
            'entry_fee'        => 200000,
            'max_participants' => 16,
            'start_at'         => now()->subDays(3),
        ]);

        $futsalTeams = [
            'Garuda Muda', 'Elang Biru', 'Singa Emas', 'Harimau Jawa',
            'Rajawali FC', 'Badak Hitam', 'Merak Bali', 'Banteng Solo',
            'Kuda Laut', 'Pesut Borneo', 'Cendrawasih Papua', 'Komodo NTT',
            'Rusa Sulawesi', 'Tapir Sumatera', 'Anoa Sulsel', 'Kasuari Papua',
        ];
        $this->createTeamParticipants($t, $players, $futsalTeams, 0);

        // Stage 1: Group Phase
        $groupStage = Stage::create([
            'tournament_id'        => $t->id,
            'name'                 => 'Fase Grup',
            'type'                 => 'round_robin',
            'status'               => 'pending',
            'order'                => 1,
            'groups_count'         => 4,
            'participants_per_group' => 4,
            'participants_advance' => 8,
            'settings'             => [
                'match_format'   => 'single_game',
                'win_condition'  => 1,
                'scoring_method' => 'point_based',
                'advance_count'  => 8,
                'rules'          => ['allow_draw' => true, 'extra_time' => false, 'penalties' => false],
            ],
        ]);

        $this->stageService->startStage($groupStage);
        $this->matchService->autoScheduleMatches($groupStage, [
            'start_at'         => $t->start_at->setHour(9),
            'interval_minutes' => 60,
            'matches_per_day'  => 6,
        ]);

        // Stage 2: Knockout (pending, not started)
        Stage::create([
            'tournament_id' => $t->id,
            'name'          => 'Babak Knockout',
            'type'          => 'single_elim',
            'status'        => 'pending',
            'order'         => 2,
            'participants_advance' => 1,
            'settings'      => [
                'match_format'   => 'single_game',
                'win_condition'  => 1,
                'scoring_method' => 'result_based',
                'advance_count'  => 1,
                'rules'          => ['allow_draw' => false, 'extra_time' => true, 'penalties' => true],
            ],
        ]);

        $this->command->info('  ✓ Futsal Nusantara Cup – Round Robin Multi-Group, Single Game, Point Based');
    }

    // ─────────────────────────────────────────
    // 7. FINISHED TOURNAMENTS
    // ─────────────────────────────────────────
    private function seedFinished($organizer, $players): void
    {
        $sport = Sport::first();
        if (!$sport) return;

        // A) Finished Single Elim – BO1 – Score Based
        $t1 = Tournament::create([
            'user_id'          => $organizer->id,
            'sport_id'         => $sport->id,
            'title'            => 'Juara League: The Last Stand',
            'slug'             => 'jl-finished-single-' . Str::random(4),
            'status'           => 'ongoing',
            'mode'             => 'open',
            'bracket_type'     => 'single',
            'participant_type' => 'individual',
            'max_participants' => 8,
            'start_at'         => now()->subDays(30),
        ]);

        $this->createIndividualParticipants($t1, $players->shuffle()->take(8));

        $s1 = Stage::create([
            'tournament_id' => $t1->id,
            'name'          => 'Main Bracket',
            'type'          => 'single_elim',
            'status'        => 'pending',
            'order'         => 1,
            'participants_advance' => 1,
            'settings'      => [
                'match_format'   => 'best_of',
                'win_condition'  => 1,  // BO1
                'scoring_method' => 'score_based',
                'advance_count'  => 1,
                'rules'          => ['allow_draw' => false, 'extra_time' => false, 'penalties' => false],
            ],
        ]);
        $this->stageService->startStage($s1);
        $this->matchService->autoScheduleMatches($s1, ['start_at' => $t1->start_at->copy()]);
        $this->simulateCompleted($s1);
        $t1->update(['status' => 'completed']);

        // B) Finished Double Elim – BO3 – Score Based
        $t2 = Tournament::create([
            'user_id'          => $organizer->id,
            'sport_id'         => $sport->id,
            'title'            => 'Juara League: Rebirth',
            'slug'             => 'jl-finished-double-' . Str::random(4),
            'status'           => 'ongoing',
            'mode'             => 'open',
            'bracket_type'     => 'double',
            'participant_type' => 'individual',
            'max_participants' => 8,
            'start_at'         => now()->subDays(20),
        ]);

        $this->createIndividualParticipants($t2, $players->shuffle()->take(8));

        $s2 = Stage::create([
            'tournament_id' => $t2->id,
            'name'          => 'Main Event',
            'type'          => 'double_elim',
            'status'        => 'pending',
            'order'         => 1,
            'participants_advance' => 1,
            'settings'      => [
                'match_format'   => 'best_of',
                'win_condition'  => 2,  // BO3
                'scoring_method' => 'score_based',
                'advance_count'  => 1,
                'rules'          => ['allow_draw' => false, 'extra_time' => false, 'penalties' => false],
            ],
        ]);
        $this->stageService->startStage($s2);
        $this->matchService->autoScheduleMatches($s2, ['start_at' => $t2->start_at->copy()]);
        $this->simulateCompleted($s2);
        $t2->update(['status' => 'completed']);

        $this->command->info('  ✓ Finished Tournaments (Single BO1 + Double BO3)');
    }

    // ─────────────────────────────────────────
    // 8. RANDOM / DRAFT
    // ─────────────────────────────────────────
    private function seedRandom($organizer, $players): void
    {
        $sports = Sport::all();

        Tournament::factory()->count(2)->create([
            'user_id'  => $organizer->id,
            'status'   => 'draft',
            'sport_id' => $sports->random()->id,
        ]);

        Tournament::factory()->count(4)->create([
            'sport_id' => $sports->random()->id,
        ]);

        $this->command->info('  ✓ Random & Draft Tournaments');
    }

    // ─────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────

    private function createTeamParticipants($tournament, $players, array $teamNames, int $offset): void
    {
        foreach ($teamNames as $i => $name) {
            $captain = $players->values()->get($offset + $i) ?? $players->first();
            $team = Team::create([
                'name'       => $name,
                'slug'       => Str::slug($name) . '-' . Str::random(3),
                'captain_id' => $captain->id,
                'status'     => 'approved',
            ]);
            Participant::create([
                'tournament_id' => $tournament->id,
                'user_id'       => $captain->id,
                'team_id'       => $team->id,
                'status'        => 'approved',
                'payment_status' => 'paid',
            ]);
        }
    }

    private function createIndividualParticipants($tournament, $players): void
    {
        foreach ($players as $player) {
            Participant::create([
                'tournament_id' => $tournament->id,
                'user_id'       => $player->id,
                'status'        => 'approved',
                'payment_status' => 'paid',
            ]);
        }
    }

    private function simulateCompleted(Stage $stage): void
    {
        $safe = 100;
        while ($safe-- > 0) {
            // 1. Find matches that can be started (upcoming with both participants)
            $playable = $stage->matches()
                ->where('status', 'upcoming')
                ->whereHas('matchParticipants', fn($q) => $q->where('slot', 1))
                ->whereHas('matchParticipants', fn($q) => $q->where('slot', 2))
                ->get();

            foreach ($playable as $match) {
                $this->matchService->updateMatch($match, ['status' => 'ongoing']);
            }

            // 2. Find ongoing matches and play a game
            $ongoing = $stage->matches()
                ->where('status', 'ongoing')
                ->with(['matchParticipants', 'games'])
                ->get();

            if ($playable->isEmpty() && $ongoing->isEmpty()) break;

            foreach ($ongoing as $match) {
                $p1 = $match->matchParticipants->firstWhere('slot', 1);
                $p2 = $match->matchParticipants->firstWhere('slot', 2);

                if (!$p1 || !$p2) continue;

                $nextGameNum = ($match->games->max('game_number') ?? 0) + 1;
                
                // Randomly pick a winner for this game
                $winnerParticipantId = rand(0, 1) === 0 ? $p1->participant_id : $p2->participant_id;
                $s1 = $winnerParticipantId === $p1->participant_id ? rand(10, 13) : rand(0, 9);
                $s2 = $winnerParticipantId === $p2->participant_id ? rand(10, 13) : rand(0, 9);

                $this->matchService->inputGameResult($match, [
                    'game_number'  => $nextGameNum,
                    'winner_id'    => $winnerParticipantId,
                    'score_p1'     => $s1,
                    'score_p2'     => $s2,
                ]);
            }
        }
    }
}
