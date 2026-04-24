<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Participant;
use App\Models\Stage;
use App\Models\TournamentMatch;
use App\Services\BracketGenerators\SwissGenerator;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatchService
{
    /**
     * Auto-schedule matches for a stage based on settings.
     *
     * @param Stage $stage
     * @param array $settings [start_at, interval_minutes, matches_per_day]
     * @return int Number of matches scheduled
     */
    public function autoScheduleMatches(Stage $stage, array $settings): int
    {
        $startAt = isset($settings['start_at']) ? Carbon::parse($settings['start_at']) : $stage->tournament->start_at;
        $intervalMinutes = $settings['interval_minutes'] ?? 120; // Default 2 hours
        $matchesPerDay = $settings['matches_per_day'] ?? 8;
        
        $matches = $stage->matches()
            ->where('status', '!=', 'bye')
            ->orderBy('round')
            ->orderBy('match_number')
            ->get();

        $scheduledCount = 0;
        $currentDateTime = $startAt->copy();
        $dailyMatchCount = 0;

        foreach ($matches as $match) {
            // Apply scheduling
            $match->update(['scheduled_at' => $currentDateTime]);
            $scheduledCount++;

            // Increment for next match
            $dailyMatchCount++;
            
            if ($dailyMatchCount >= $matchesPerDay) {
                // Next day, reset clock to startAt time
                $currentDateTime->addDay()->setTime($startAt->hour, $startAt->minute);
                $dailyMatchCount = 0;
            } else {
                $currentDateTime->addMinutes($intervalMinutes);
            }
        }

        return $scheduledCount;
    }

    /**
     * Get all matches for a stage with optional filters.
     */
    public function getMatchesByStage(Stage $stage, array $filters = []): Collection
    {
        $query = $stage->matches()
            ->with([
                'matchParticipants.participant.user',
                'matchParticipants.participant.team',
                'winner.user', 'winner.team',
                'games',
            ]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['round'])) {
            $query->where('round', $filters['round']);
        }

        if (!empty($filters['participant_id'])) {
            $pid = $filters['participant_id'];
            $query->whereHas('matchParticipants', function ($q) use ($pid) {
                $q->where('participant_id', $pid);
            });
        }

        return $query->orderBy('round')->orderBy('match_number')->get();
    }

    /**
     * Get match detail with games.
     */
    public function getMatchDetail(TournamentMatch $match): TournamentMatch
    {
        $match->load([
            'matchParticipants.participant.user',
            'matchParticipants.participant.team',
            'winner.user', 'winner.team',
            'games',
        ]);

        return $match;
    }

    /**
     * Update match status or schedule.
     */
    public function updateMatch(TournamentMatch $match, array $data): TournamentMatch
    {
        // Validate status transitions
        if (isset($data['status'])) {
            $this->validateStatusTransition($match, $data['status']);
        }

        $match->update($data);
        return $match;
    }

    /**
     * Input a game result for a match.
     */
    public function inputGameResult(TournamentMatch $match, array $data): array
    {
        // Validate match is ongoing
        if (!$match->isOngoing()) {
            throw ValidationException::withMessages([
                'match' => ['Match harus berstatus ongoing untuk input hasil.'],
            ]);
        }

        $gameNumber = $data['game_number'];
        $winnerId = $data['winner_id'];
        $scoreP1 = $data['score_p1'] ?? null;
        $scoreP2 = $data['score_p2'] ?? null;

        // Validate game number is sequential
        $lastGame = $match->games()->max('game_number') ?? 0;
        if ($gameNumber !== $lastGame + 1) {
            throw ValidationException::withMessages([
                'game_number' => ['Nomor game harus berurutan. Game berikutnya: ' . ($lastGame + 1)],
            ]);
        }

        // Validate winner is one of the participants
        if (!$match->hasParticipant($winnerId)) {
            throw ValidationException::withMessages([
                'winner_id' => ['Pemenang harus salah satu peserta match.'],
            ]);
        }

        // Get config from stage settings
        $stage = $match->stage;
        $format = $stage->getMatchFormat();
        $scoringMethod = $stage->getScoringMethod();
        $winCondition = $stage->getWinCondition();

        return DB::transaction(function () use ($match, $gameNumber, $winnerId, $scoreP1, $scoreP2, $winCondition, $format) {
            // Create game record
            $game = Game::create([
                'match_id' => $match->id,
                'game_number' => $gameNumber,
                'winner_id' => $winnerId,
                'score_p1' => $scoreP1,
                'score_p2' => $scoreP2,
                'status' => 'created',
            ]);

            // Update wins in match_participants
            $winnerParticipant = $match->matchParticipants()->where('participant_id', $winnerId)->first();
            $winnerParticipant->increment('score'); // Using 'score' column to track game wins in BO

            // Recalculate match state
            $participants = $match->matchParticipants()->orderBy('slot')->get();
            $matchWinner = null;
            $matchCompleted = false;

            if ($format === 'best_of') {
                foreach ($participants as $p) {
                    if ($p->score >= $winCondition) {
                        $matchWinner = $p->participant_id;
                        $matchCompleted = true;
                        break;
                    }
                }
            } elseif ($format === 'single_game') {
                $matchWinner = $winnerId;
                $matchCompleted = true;
            }

            $scores = $participants->mapWithKeys(function($p, $index) {
                return ["participant_" . ($index + 1) => $p->score];
            })->toArray();

            if ($matchCompleted) {
                $match->update([
                    'status' => 'completed',
                    'winner_id' => $matchWinner,
                    'scores' => $scores,
                    'completed_at' => now(),
                ]);

                // Mark winner in pivot table
                $match->matchParticipants()->where('participant_id', $matchWinner)->update(['is_winner' => true]);

                // Auto-advance winner and handle bracket updates
                $this->handleMatchCompletion($match);
            } else {
                // Update running score
                $match->update([
                    'scores' => $scores,
                ]);
            }

            return [
                'game' => $game->load('winner'),
                'match_status' => $match->fresh()->status,
                'current_score' => $scores,
                'match_winner' => $matchWinner ? Participant::with(['user', 'team'])->find($matchWinner) : null,
                'next_match_id' => $matchCompleted ? $match->next_match_winner_id : null,
            ];
        });
    }

    /**
     * Correct a game result (Owner only).
     */
    public function correctGameResult(Game $game, array $data): array
    {
        $match = $game->match;

        // Cannot correct if winner has already played in next match
        if ($match->winner_id && $match->next_match_winner_id) {
            $nextMatch = TournamentMatch::find($match->next_match_winner_id);
            if ($nextMatch && $nextMatch->status !== 'upcoming') {
                throw ValidationException::withMessages([
                    'game' => ['Koreksi tidak bisa dilakukan karena pemenang sudah bermain di match berikutnya.'],
                ]);
            }
        }

        // Cannot correct if stage is completed
        $stage = $match->stage;
        if ($stage->isCompleted()) {
            throw ValidationException::withMessages([
                'game' => ['Koreksi tidak bisa dilakukan karena stage sudah selesai.'],
            ]);
        }

        return DB::transaction(function () use ($game, $match, $data) {
            // Update game
            $game->update([
                'winner_id' => $data['winner_id'],
                'score_p1' => $data['score_p1'] ?? $game->score_p1,
                'score_p2' => $data['score_p2'] ?? $game->score_p2,
                'status' => 'corrected',
            ]);

            // Recalculate match winner
            $stage = $match->stage;
            $winCondition = $stage->getWinCondition();
            $format = $stage->getMatchFormat();

            $participants = $match->matchParticipants()->orderBy('slot')->get();
            $matchWinner = null;
            
            // Re-sync scores from match_participants table
            foreach ($participants as $p) {
                $p->update(['score' => $match->games()->where('winner_id', $p->participant_id)->count()]);
            }

            if ($format === 'best_of') {
                foreach ($participants as $p) {
                    if ($p->score >= $winCondition) {
                        $matchWinner = $p->participant_id;
                        break;
                    }
                }
            } elseif ($format === 'single_game') {
                $matchWinner = $data['winner_id'];
            }

            // Update match scores and potentially new winner
            $oldWinner = $match->winner_id;
            $scores = $participants->mapWithKeys(function($p, $index) {
                return ["participant_" . ($index + 1) => $p->score];
            })->toArray();

            $match->update([
                'winner_id' => $matchWinner,
                'scores' => $scores,
            ]);

            // Mark winner in pivot table
            $match->matchParticipants()->update(['is_winner' => false]);
            if ($matchWinner) {
                $match->matchParticipants()->where('participant_id', $matchWinner)->update(['is_winner' => true]);
            }

            // If winner changed, update bracket
            if ($oldWinner !== $matchWinner && $matchWinner) {
                $this->updateNextMatchParticipant($match, $oldWinner, $matchWinner);
            }

            return [
                'game' => $game->fresh()->load('winner'),
                'match_winner' => $matchWinner ? Participant::with(['user', 'team'])->find($matchWinner) : null,
            ];
        });
    }

    /**
     * Handle automatic updates after a match is completed.
     */
    protected function handleMatchCompletion(TournamentMatch $match): void
    {
        $stage = $match->stage;
        $winnerId = $match->winner_id;
        
        // Find loser(s)
        $pIds = $match->matchParticipants()->pluck('participant_id')->toArray();
        $loserIds = collect($pIds)->reject(fn($id) => $id === $winnerId)->values()->toArray();
        $loserId = $loserIds[0] ?? null;

        // Advance winner to next match
        if ($match->next_match_winner_id && $winnerId) {
            $nextMatch = TournamentMatch::find($match->next_match_winner_id);
            if ($nextMatch) {
                // Check if already in next match
                if (!$nextMatch->hasParticipant($winnerId)) {
                    $existingCount = $nextMatch->matchParticipants()->count();
                    $nextMatch->matchParticipants()->create([
                        'participant_id' => $winnerId,
                        'slot' => $existingCount + 1
                    ]);
                }
            }
        }

        // For double elimination: send loser to lower bracket
        if ($match->next_match_loser_id && $loserId) {
            $loserMatch = TournamentMatch::find($match->next_match_loser_id);
            if ($loserMatch) {
                if (!$loserMatch->hasParticipant($loserId)) {
                    $existingCount = $loserMatch->matchParticipants()->count();
                    $loserMatch->matchParticipants()->create([
                        'participant_id' => $loserId,
                        'slot' => $existingCount + 1
                    ]);
                }
            }
        }

        // Check if all matches in stage are completed → auto-complete stage
        $incompleteMatches = $stage->matches()
            ->whereNotIn('status', ['completed', 'bye'])
            ->count();

        if ($incompleteMatches === 0) {
            $stage->update(['status' => 'completed']);
        }

        // For Swiss: check if round is complete → generate next round
        if ($stage->type === 'swiss') {
            $currentRound = $match->round;
            $roundIncomplete = $stage->matches()
                ->where('round', $currentRound)
                ->whereNotIn('status', ['completed', 'bye'])
                ->count();

            if ($roundIncomplete === 0) {
                $maxRounds = $stage->settings['rounds'] ?? (int) ceil(log($stage->tournament->participants()->where('status', 'approved')->count(), 2)) + 2;
                if ($currentRound < $maxRounds && $incompleteMatches > 0) {
                    (new SwissGenerator())->generateNextRound($stage, $currentRound + 1);
                }
            }
        }
    }

    /**
     * Update next match participant when a correction changes the winner.
     */
    protected function updateNextMatchParticipant(TournamentMatch $match, ?string $oldWinnerId, string $newWinnerId): void
    {
        if (!$match->next_match_winner_id) {
            return;
        }

        $nextMatch = TournamentMatch::find($match->next_match_winner_id);
        if (!$nextMatch) {
            return;
        }

        // Find the participant record in next match and update it
        $nextMatch->matchParticipants()
            ->where('participant_id', $oldWinnerId)
            ->update(['participant_id' => $newWinnerId]);
    }

    /**
     * Validate match status transitions.
     */
    protected function validateStatusTransition(TournamentMatch $match, string $newStatus): void
    {
        $allowed = match ($match->status) {
            'upcoming' => ['ongoing'],
            'ongoing' => ['completed'],
            default => [],
        };

        if (!in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => ["Tidak bisa mengubah status dari '{$match->status}' ke '{$newStatus}'."],
            ]);
        }

        // Cannot start match with disqualified participant
        if ($newStatus === 'ongoing') {
            $pIds = $match->matchParticipants()->pluck('participant_id');
            $disqualifiedCount = Participant::whereIn('id', $pIds)
                ->where('status', 'disqualified')
                ->count();

            if ($disqualifiedCount > 0) {
                throw ValidationException::withMessages([
                    'status' => ['Salah satu peserta telah didiskualifikasi.'],
                ]);
            }
        }
    }

    /**
     * Get max games based on BO format.
     */
    protected function getMaxGames(string $boFormat): int
    {
        return match ($boFormat) {
            'bo1' => 1,
            'bo3' => 3,
            'bo5' => 5,
            'bo7' => 7,
            default => 1,
        };
    }

    /**
     * Get win condition (number of wins needed) based on BO format.
     */
    protected function getWinCondition(string $boFormat): int
    {
        return match ($boFormat) {
            'bo1' => 1,
            'bo3' => 2,
            'bo5' => 3,
            'bo7' => 4,
            default => 1,
        };
    }
}
