<?php

namespace App\Services\BracketGenerators;

use App\Models\Participant;
use App\Models\Stage;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;

class SwissGenerator
{
    /**
     * Generate the first round of a Swiss tournament.
     * Subsequent rounds are generated after each round completes,
     * via the `generateNextRound` method.
     *
     * @param Stage $stage
     * @param Collection<Participant> $participants Already seeded/ordered
     * @return int Number of matches generated
     */
    public function generate(Stage $stage, Collection $participants): int
    {
        // Swiss round 1: pair based on initial seeding
        // Top half vs bottom half
        return $this->generateRoundBySeeding($stage, $participants, 1);
    }

    /**
     * Generate a Swiss round by pairing top half vs bottom half of ordered participants.
     */
    protected function generateRoundBySeeding(Stage $stage, Collection $participants, int $round): int
    {
        $count = $participants->count();
        $matchCount = 0;
        $half = intdiv($count, 2);
        $players = $participants->values();

        for ($i = 0; $i < $half; $i++) {
            $matchCount++;
            $match = TournamentMatch::create([
                'stage_id' => $stage->id,
                'round' => $round,
                'match_number' => $matchCount,
                'status' => 'upcoming',
            ]);

            $match->matchParticipants()->create(['participant_id' => $players[$i]->id, 'slot' => 1]);
            $match->matchParticipants()->create(['participant_id' => $players[$half + $i]->id, 'slot' => 2]);
        }

        // Handle bye for odd number of participants
        if ($count % 2 !== 0) {
            $byeParticipant = $players->last();
            $matchCount++;
            $match = TournamentMatch::create([
                'stage_id' => $stage->id,
                'round' => $round,
                'match_number' => $matchCount,
                'winner_id' => $byeParticipant->id,
                'status' => 'bye',
            ]);
            $match->matchParticipants()->create(['participant_id' => $byeParticipant->id, 'slot' => 1, 'is_winner' => true]);
        }

        return $matchCount;
    }

    /**
     * Generate the next Swiss round...
     */
    public function generateNextRound(Stage $stage, int $roundNumber): int
    {
        $standings = $this->calculateStandings($stage);
        $matchCount = 0;
        $paired = [];
        $previousPairings = $this->getPreviousPairings($stage);

        // Sort by points descending
        $sorted = $standings->sortByDesc('points')->values();

        for ($i = 0; $i < $sorted->count(); $i++) {
            $p1 = $sorted[$i];

            if (in_array($p1['participant_id'], $paired)) {
                continue;
            }

            // Find best opponent: closest points, not yet paired, no rematch
            $bestOpponent = null;
            for ($j = $i + 1; $j < $sorted->count(); $j++) {
                $p2 = $sorted[$j];
                if (in_array($p2['participant_id'], $paired)) {
                    continue;
                }

                // Check for rematch
                $pairingKey = $this->pairingKey($p1['participant_id'], $p2['participant_id']);
                if (in_array($pairingKey, $previousPairings)) {
                    continue;
                }

                $bestOpponent = $p2;
                break;
            }

            if ($bestOpponent) {
                $matchCount++;
                $match = TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'round' => $roundNumber,
                    'match_number' => $matchCount,
                    'status' => 'upcoming',
                ]);

                $match->matchParticipants()->create(['participant_id' => $p1['participant_id'], 'slot' => 1]);
                $match->matchParticipants()->create(['participant_id' => $bestOpponent['participant_id'], 'slot' => 2]);

                $paired[] = $p1['participant_id'];
                $paired[] = $bestOpponent['participant_id'];
            } else {
                // No valid opponent found — give bye
                $matchCount++;
                $match = TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'round' => $roundNumber,
                    'match_number' => $matchCount,
                    'winner_id' => $p1['participant_id'],
                    'status' => 'bye',
                ]);

                $match->matchParticipants()->create(['participant_id' => $p1['participant_id'], 'slot' => 1, 'is_winner' => true]);
                $paired[] = $p1['participant_id'];
            }
        }

        return $matchCount;
    }

    /**
     * Calculate standings for a Swiss stage.
     */
    public function calculateStandings(Stage $stage): Collection
    {
        $matches = $stage->matches()
            ->with('matchParticipants')
            ->whereIn('status', ['completed', 'bye'])
            ->get();

        $stats = [];

        foreach ($matches as $match) {
            $pIds = $match->matchParticipants->pluck('participant_id')->toArray();
            foreach ($pIds as $pid) {
                if (!$pid) continue;
                if (!isset($stats[$pid])) {
                    $stats[$pid] = [
                        'participant_id' => $pid,
                        'points' => 0,
                        'wins' => 0,
                        'losses' => 0,
                        'opponents' => [],
                    ];
                }
            }

            if ($match->status === 'bye') {
                $p1 = $match->matchParticipants->first();
                if ($p1) {
                    $stats[$p1->participant_id]['points'] += 3;
                    $stats[$p1->participant_id]['wins']++;
                }
                continue;
            }

            if ($match->winner_id) {
                $winnerId = $match->winner_id;
                $loserId = collect($pIds)->reject(fn($id) => $id === $winnerId)->first();

                if ($winnerId && isset($stats[$winnerId])) {
                    $stats[$winnerId]['points'] += 3;
                    $stats[$winnerId]['wins']++;
                    if ($loserId) {
                        $stats[$winnerId]['opponents'][] = $loserId;
                    }
                }
                if ($loserId && isset($stats[$loserId])) {
                    $stats[$loserId]['losses']++;
                    if ($winnerId) {
                        $stats[$loserId]['opponents'][] = $winnerId;
                    }
                }
            }
        }

        // Calculate Buchholz Score
        foreach ($stats as &$stat) {
            $stat['buchholz'] = 0;
            foreach ($stat['opponents'] as $oppId) {
                $stat['buchholz'] += $stats[$oppId]['points'] ?? 0;
            }
            unset($stat['opponents']);
        }

        return collect(array_values($stats));
    }

    /**
     * Get all previous pairings to avoid rematches.
     */
    protected function getPreviousPairings(Stage $stage): array
    {
        return $stage->matches()
            ->with('matchParticipants')
            ->get()
            ->map(function($m) {
                $pIds = $m->matchParticipants->pluck('participant_id')->sort()->values()->toArray();
                if (count($pIds) < 2) return null;
                return "{$pIds[0]}:{$pIds[1]}";
            })
            ->filter()
            ->toArray();
    }

    /**
     * Create a consistent pairing key regardless of order.
     */
    protected function pairingKey(string $a, string $b): string
    {
        return $a < $b ? "{$a}:{$b}" : "{$b}:{$a}";
    }
}
