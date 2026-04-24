<?php

namespace App\Services\BracketGenerators;

use App\Models\Participant;
use App\Models\Stage;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;

class DoubleEliminationGenerator
{
    /**
     * Generate a double elimination bracket for the given stage.
     *
     * @param Stage $stage
     * @param Collection<Participant> $participants Already seeded/ordered
     * @return int Number of matches generated
     */
    public function generate(Stage $stage, Collection $participants): int
    {
        $count = $participants->count();
        $totalUpperRounds = (int) ceil(log($count, 2));
        $bracketSize = (int) pow(2, $totalUpperRounds);

        $matchNumber = 0;
        $upperMatches = [];
        $lowerMatches = [];

        // ── Upper Bracket ──
        for ($round = $totalUpperRounds; $round >= 1; $round--) {
            $matchesInRound = (int) pow(2, $totalUpperRounds - $round);
            for ($i = 0; $i < $matchesInRound; $i++) {
                $matchNumber++;
                $upperMatches[$round][$i] = TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'round' => $round,
                    'match_number' => $matchNumber,
                    'status' => 'upcoming',
                    'bracket_side' => 'upper',
                ]);
            }
        }

        // Link upper bracket: winner advances to next upper round
        for ($round = 1; $round < $totalUpperRounds; $round++) {
            foreach ($upperMatches[$round] as $i => $match) {
                $nextIndex = intdiv($i, 2);
                if (isset($upperMatches[$round + 1][$nextIndex])) {
                    $match->update([
                        'next_match_winner_id' => $upperMatches[$round + 1][$nextIndex]->id,
                    ]);
                }
            }
        }

        // ── Lower Bracket ──
        // Lower bracket has (totalUpperRounds - 1) * 2 rounds
        // But simplified: we create enough rounds for losers to come down
        $totalLowerRounds = ($totalUpperRounds - 1) * 2;

        if ($totalLowerRounds > 0) {
            // Lower round 1: receives losers from upper round 1
            $lowerR1MatchCount = (int) pow(2, $totalUpperRounds - 2);
            for ($i = 0; $i < max(1, $lowerR1MatchCount); $i++) {
                $matchNumber++;
                $lowerMatches[1][$i] = TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'round' => $totalUpperRounds + 1, // offset to distinguish from upper
                    'match_number' => $matchNumber,
                    'status' => 'upcoming',
                    'bracket_side' => 'lower',
                ]);
            }

            // Link upper round 1 losers to lower round 1
            if (isset($upperMatches[1])) {
                foreach ($upperMatches[1] as $i => $match) {
                    $lowerIndex = intdiv($i, 2);
                    if (isset($lowerMatches[1][$lowerIndex])) {
                        $match->update([
                            'next_match_loser_id' => $lowerMatches[1][$lowerIndex]->id,
                        ]);
                    }
                }
            }

            // Build remaining lower bracket rounds
            $currentLowerRound = 1;
            for ($lr = 2; $lr <= $totalLowerRounds; $lr++) {
                $prevCount = isset($lowerMatches[$lr - 1]) ? count($lowerMatches[$lr - 1]) : 1;
                // Every other lower round halves the match count
                $thisCount = ($lr % 2 === 0) ? $prevCount : max(1, intdiv($prevCount, 2));

                for ($i = 0; $i < $thisCount; $i++) {
                    $matchNumber++;
                    $lowerMatches[$lr][$i] = TournamentMatch::create([
                        'stage_id' => $stage->id,
                        'round' => $totalUpperRounds + $lr,
                        'match_number' => $matchNumber,
                        'status' => 'upcoming',
                        'bracket_side' => 'lower',
                    ]);
                }

                // Link previous lower round winners to this round
                if (isset($lowerMatches[$lr - 1])) {
                    foreach ($lowerMatches[$lr - 1] as $i => $match) {
                        $nextIndex = ($lr % 2 === 0) ? $i : intdiv($i, 2);
                        if (isset($lowerMatches[$lr][$nextIndex])) {
                            $match->update([
                                'next_match_winner_id' => $lowerMatches[$lr][$nextIndex]->id,
                            ]);
                        }
                    }
                }
            }

            // Link upper bracket losers (round 2+) into lower bracket
            for ($round = 2; $round <= $totalUpperRounds; $round++) {
                if (!isset($upperMatches[$round])) continue;
                // Determine which lower round receives these losers
                $targetLowerRound = ($round - 1) * 2;
                foreach ($upperMatches[$round] as $i => $match) {
                    if (isset($lowerMatches[$targetLowerRound][$i])) {
                        $match->update([
                            'next_match_loser_id' => $lowerMatches[$targetLowerRound][$i]->id,
                        ]);
                    }
                }
            }
        }

        // ── Grand Final ──
        $matchNumber++;
        $grandFinal = TournamentMatch::create([
            'stage_id' => $stage->id,
            'round' => $totalUpperRounds + $totalLowerRounds + 1,
            'match_number' => $matchNumber,
            'status' => 'upcoming',
            'bracket_side' => 'grand_final',
        ]);

        // Link upper bracket final winner → grand final
        if (isset($upperMatches[$totalUpperRounds][0])) {
            $upperMatches[$totalUpperRounds][0]->update([
                'next_match_winner_id' => $grandFinal->id,
            ]);
        }

        // Link lower bracket final winner → grand final
        if (isset($lowerMatches[$totalLowerRounds][0])) {
            $lowerMatches[$totalLowerRounds][0]->update([
                'next_match_winner_id' => $grandFinal->id,
            ]);
        }

        // ── Grand Final Reset (optional match) ──
        $matchNumber++;
        $grandFinalReset = TournamentMatch::create([
            'stage_id' => $stage->id,
            'round' => $totalUpperRounds + $totalLowerRounds + 2,
            'match_number' => $matchNumber,
            'status' => 'upcoming',
            'bracket_side' => 'grand_final',
        ]);

        $grandFinal->update([
            'next_match_winner_id' => $grandFinalReset->id,
        ]);

        // ── Seed participants into upper bracket round 1 ──
        $seeded = $this->seedBracket($participants, $bracketSize);
        $round1Matches = $upperMatches[1] ?? [];

        foreach ($round1Matches as $i => $match) {
            $p1 = $seeded[$i * 2] ?? null;
            $p2 = $seeded[$i * 2 + 1] ?? null;

            // Add participants to match_participants
            if ($p1) {
                $match->matchParticipants()->create([
                    'participant_id' => $p1->id,
                    'slot' => 1
                ]);
            }
            if ($p2) {
                $match->matchParticipants()->create([
                    'participant_id' => $p2->id,
                    'slot' => 2
                ]);
            }

            if ($p1 && !$p2) {
                $match->update([
                    'winner_id' => $p1->id,
                    'status' => 'bye'
                ]);
                $match->matchParticipants()->where('participant_id', $p1->id)->update(['is_winner' => true]);
                $this->advanceWinnerToNextMatch($match, $p1);
            } elseif ($p2 && !$p1) {
                $match->update([
                    'winner_id' => $p2->id,
                    'status' => 'bye'
                ]);
                $match->matchParticipants()->where('participant_id', $p2->id)->update(['is_winner' => true]);
                $this->advanceWinnerToNextMatch($match, $p2);
            }
        }

        return $matchNumber;
    }

    /**
     * Advance the winner of a bye/completed match to their next match.
     */
    protected function advanceWinnerToNextMatch(TournamentMatch $match, Participant $winner): void
    {
        if (!$match->next_match_winner_id) {
            return;
        }

        $nextMatch = TournamentMatch::find($match->next_match_winner_id);
        if (!$nextMatch) {
            return;
        }

        // Determine which slot the winner should go to in the next match
        // For double elim, it's usually based on the index in the current round
        $existingCount = $nextMatch->matchParticipants()->count();
        
        $nextMatch->matchParticipants()->create([
            'participant_id' => $winner->id,
            'slot' => $existingCount + 1
        ]);
    }

    /**
     * Seed participants into bracket positions using standard seeding.
     */
    protected function seedBracket(Collection $participants, int $bracketSize): array
    {
        $positions = $this->generateSeedPositions($bracketSize);
        $result = array_fill(0, $bracketSize, null);

        foreach ($participants->values() as $index => $participant) {
            if (isset($positions[$index])) {
                $result[$positions[$index]] = $participant;
            }
        }

        return $result;
    }

    /**
     * Generate standard tournament seed positions.
     */
    protected function generateSeedPositions(int $size): array
    {
        if ($size === 1) {
            return [0];
        }

        $positions = [0, 1];

        while (count($positions) < $size) {
            $newPositions = [];
            $currentSize = count($positions);
            foreach ($positions as $pos) {
                $newPositions[] = $pos;
                $newPositions[] = $currentSize * 2 - 1 - $pos;
            }
            $positions = $newPositions;
        }

        return $positions;
    }
}
