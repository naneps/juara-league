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
            ->with(['participant1.user', 'participant1.team',
                    'participant2.user', 'participant2.team',
                    'winner', 'games']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['round'])) {
            $query->where('round', $filters['round']);
        }

        if (!empty($filters['participant_id'])) {
            $pid = $filters['participant_id'];
            $query->where(function ($q) use ($pid) {
                $q->where('participant_1_id', $pid)
                  ->orWhere('participant_2_id', $pid);
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
            'participant1.user', 'participant1.team',
            'participant2.user', 'participant2.team',
            'winner', 'games.winner',
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
        if ($winnerId !== $match->participant_1_id && $winnerId !== $match->participant_2_id) {
            throw ValidationException::withMessages([
                'winner_id' => ['Pemenang harus salah satu peserta match.'],
            ]);
        }

        // Get BO format from stage
        $boFormat = $match->stage->bo_format ?? 'bo1';
        $maxGames = $this->getMaxGames($boFormat);
        $winCondition = $this->getWinCondition($boFormat);

        // Validate not exceeding max games
        if ($gameNumber > $maxGames) {
            throw ValidationException::withMessages([
                'game_number' => ["Tidak bisa input lebih dari {$maxGames} game untuk format {$boFormat}."],
            ]);
        }

        return DB::transaction(function () use ($match, $gameNumber, $winnerId, $scoreP1, $scoreP2, $winCondition) {
            // Create game record
            $game = Game::create([
                'match_id' => $match->id,
                'game_number' => $gameNumber,
                'winner_id' => $winnerId,
                'score_p1' => $scoreP1,
                'score_p2' => $scoreP2,
                'status' => 'created',
            ]);

            // Count wins per participant
            $p1Wins = $match->games()->where('winner_id', $match->participant_1_id)->count();
            $p2Wins = $match->games()->where('winner_id', $match->participant_2_id)->count();

            $matchWinner = null;
            $matchCompleted = false;

            // Check win condition
            if ($p1Wins >= $winCondition) {
                $matchWinner = $match->participant_1_id;
                $matchCompleted = true;
            } elseif ($p2Wins >= $winCondition) {
                $matchWinner = $match->participant_2_id;
                $matchCompleted = true;
            }

            if ($matchCompleted) {
                $match->update([
                    'status' => 'completed',
                    'winner_id' => $matchWinner,
                    'scores' => [
                        'participant_1' => $p1Wins,
                        'participant_2' => $p2Wins,
                    ],
                    'completed_at' => now(),
                ]);

                // Auto-advance winner and handle bracket updates
                $this->handleMatchCompletion($match);
            } else {
                // Update running score
                $match->update([
                    'scores' => [
                        'participant_1' => $p1Wins,
                        'participant_2' => $p2Wins,
                    ],
                ]);
            }

            return [
                'game' => $game->load('winner'),
                'match_status' => $match->fresh()->status,
                'current_score' => [
                    'participant_1' => $p1Wins,
                    'participant_2' => $p2Wins,
                ],
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
            $winCondition = $this->getWinCondition($match->stage->bo_format ?? 'bo1');
            $p1Wins = $match->games()->where('winner_id', $match->participant_1_id)->count();
            $p2Wins = $match->games()->where('winner_id', $match->participant_2_id)->count();

            $matchWinner = null;
            if ($p1Wins >= $winCondition) {
                $matchWinner = $match->participant_1_id;
            } elseif ($p2Wins >= $winCondition) {
                $matchWinner = $match->participant_2_id;
            }

            // Update match scores and potentially new winner
            $oldWinner = $match->winner_id;
            $match->update([
                'winner_id' => $matchWinner,
                'scores' => [
                    'participant_1' => $p1Wins,
                    'participant_2' => $p2Wins,
                ],
            ]);

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
        $loserId = $match->participant_1_id === $winnerId
            ? $match->participant_2_id
            : $match->participant_1_id;

        // Advance winner to next match
        if ($match->next_match_winner_id && $winnerId) {
            $nextMatch = TournamentMatch::find($match->next_match_winner_id);
            if ($nextMatch) {
                if (!$nextMatch->participant_1_id) {
                    $nextMatch->update(['participant_1_id' => $winnerId]);
                } elseif (!$nextMatch->participant_2_id) {
                    $nextMatch->update(['participant_2_id' => $winnerId]);
                }
            }
        }

        // For double elimination: send loser to lower bracket
        if ($match->next_match_loser_id && $loserId) {
            $loserMatch = TournamentMatch::find($match->next_match_loser_id);
            if ($loserMatch) {
                if (!$loserMatch->participant_1_id) {
                    $loserMatch->update(['participant_1_id' => $loserId]);
                } elseif (!$loserMatch->participant_2_id) {
                    $loserMatch->update(['participant_2_id' => $loserId]);
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

        if ($nextMatch->participant_1_id === $oldWinnerId) {
            $nextMatch->update(['participant_1_id' => $newWinnerId]);
        } elseif ($nextMatch->participant_2_id === $oldWinnerId) {
            $nextMatch->update(['participant_2_id' => $newWinnerId]);
        }
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
            $p1 = $match->participant_1_id ? Participant::find($match->participant_1_id) : null;
            $p2 = $match->participant_2_id ? Participant::find($match->participant_2_id) : null;

            if (($p1 && $p1->status === 'disqualified') || ($p2 && $p2->status === 'disqualified')) {
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
