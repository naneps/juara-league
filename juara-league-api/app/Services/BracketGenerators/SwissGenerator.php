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
            TournamentMatch::create([
                'stage_id' => $stage->id,
                'round' => $round,
                'match_number' => $matchCount,
                'participant_1_id' => $players[$i]->id,
                'participant_2_id' => $players[$half + $i]->id,
                'status' => 'upcoming',
            ]);
        }

        // Handle bye for odd number of participants
        if ($count % 2 !== 0) {
            $byeParticipant = $players->last();
            $matchCount++;
            TournamentMatch::create([
                'stage_id' => $stage->id,
                'round' => $round,
                'match_number' => $matchCount,
                'participant_1_id' => $byeParticipant->id,
                'participant_2_id' => null,
                'winner_id' => $byeParticipant->id,
                'status' => 'bye',
            ]);
        }

        return $matchCount;
    }

    /**
     * Generate the next Swiss round based on current standings.
     * Pairs participants with the same or closest point totals,
     * avoiding rematches.
     *
     * @param Stage $stage
     * @param int $roundNumber The round number to generate
     * @return int Number of matches generated
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
                TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'round' => $roundNumber,
                    'match_number' => $matchCount,
                    'participant_1_id' => $p1['participant_id'],
                    'participant_2_id' => $bestOpponent['participant_id'],
                    'status' => 'upcoming',
                ]);

                $paired[] = $p1['participant_id'];
                $paired[] = $bestOpponent['participant_id'];
            } else {
                // No valid opponent found — give bye (only if not already given)
                $matchCount++;
                TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'round' => $roundNumber,
                    'match_number' => $matchCount,
                    'participant_1_id' => $p1['participant_id'],
                    'participant_2_id' => null,
                    'winner_id' => $p1['participant_id'],
                    'status' => 'bye',
                ]);

                $paired[] = $p1['participant_id'];
            }
        }

        return $matchCount;
    }

    /**
     * Calculate standings for a Swiss stage.
     * Returns collection with participant_id, points, buchholz, wins.
     */
    public function calculateStandings(Stage $stage): Collection
    {
        $matches = $stage->matches()
            ->whereIn('status', ['completed', 'bye'])
            ->get();

        $stats = [];

        foreach ($matches as $match) {
            foreach ([$match->participant_1_id, $match->participant_2_id] as $pid) {
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
                if ($match->participant_1_id) {
                    $stats[$match->participant_1_id]['points'] += 3;
                    $stats[$match->participant_1_id]['wins']++;
                }
                continue;
            }

            if ($match->winner_id) {
                $winnerId = $match->winner_id;
                $loserId = $match->participant_1_id === $winnerId
                    ? $match->participant_2_id
                    : $match->participant_1_id;

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

        // Calculate Buchholz Score (sum of opponents' points)
        foreach ($stats as &$stat) {
            $stat['buchholz'] = 0;
            foreach ($stat['opponents'] as $oppId) {
                $stat['buchholz'] += $stats[$oppId]['points'] ?? 0;
            }
            unset($stat['opponents']); // Clean up
        }

        return collect(array_values($stats));
    }

    /**
     * Get all previous pairings to avoid rematches.
     */
    protected function getPreviousPairings(Stage $stage): array
    {
        return $stage->matches()
            ->whereNotNull('participant_1_id')
            ->whereNotNull('participant_2_id')
            ->get()
            ->map(fn($m) => $this->pairingKey($m->participant_1_id, $m->participant_2_id))
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
