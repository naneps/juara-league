<?php

namespace App\Services\BracketGenerators;

use App\Models\Participant;
use App\Models\Stage;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;

class SingleEliminationGenerator
{
    /**
     * Generate a single elimination bracket for the given stage.
     *
     * @param Stage $stage
     * @param Collection<Participant> $participants Already seeded/ordered
     * @return int Number of matches generated
     */
    public function generate(Stage $stage, Collection $participants): int
    {
        $count = $participants->count();
        $totalRounds = (int) ceil(log($count, 2));
        $bracketSize = (int) pow(2, $totalRounds);

        // Pad participants with nulls for byes
        $seeded = $this->seedBracket($participants, $bracketSize);

        // Generate all matches round by round (start from the final, work backwards)
        $matches = [];
        $matchNumber = 0;

        // Create matches for all rounds (from final to round 1)
        // We build the bracket from top to bottom
        for ($round = $totalRounds; $round >= 1; $round--) {
            $matchesInRound = (int) pow(2, $totalRounds - $round);
            for ($i = 0; $i < $matchesInRound; $i++) {
                $matchNumber++;
                $matches[$round][$i] = TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'round' => $round,
                    'match_number' => $matchNumber,
                    'status' => 'upcoming',
                ]);
            }
        }

        // Link matches: winner of round N match goes to round N+1
        for ($round = 1; $round < $totalRounds; $round++) {
            $matchesInRound = count($matches[$round]);
            for ($i = 0; $i < $matchesInRound; $i++) {
                $nextMatchIndex = intdiv($i, 2);
                if (isset($matches[$round + 1][$nextMatchIndex])) {
                    $matches[$round][$i]->update([
                        'next_match_winner_id' => $matches[$round + 1][$nextMatchIndex]->id,
                    ]);
                }
            }
        }

        // Fill round 1 with participants and handle byes
        $round1Matches = $matches[1];
        for ($i = 0; $i < count($round1Matches); $i++) {
            $p1 = $seeded[$i * 2] ?? null;
            $p2 = $seeded[$i * 2 + 1] ?? null;

            $updateData = [
                'participant_1_id' => $p1?->id,
                'participant_2_id' => $p2?->id,
            ];

            // Handle bye: one participant is null
            if ($p1 && !$p2) {
                $updateData['winner_id'] = $p1->id;
                $updateData['status'] = 'bye';
                $this->advanceWinnerToNextMatch($round1Matches[$i], $p1);
            } elseif ($p2 && !$p1) {
                $updateData['winner_id'] = $p2->id;
                $updateData['status'] = 'bye';
                $this->advanceWinnerToNextMatch($round1Matches[$i], $p2);
            }

            $round1Matches[$i]->update($updateData);
        }

        return $matchNumber;
    }

    /**
     * Seed participants into bracket positions.
     * Uses standard tournament seeding: 1v8, 4v5, 3v6, 2v7 for 8 players.
     *
     * @param Collection $participants Ordered by seed
     * @param int $bracketSize Must be power of 2
     * @return array
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
     * Seed 1 plays the highest seed, seed 2 plays the second highest, etc.
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

        if (!$nextMatch->participant_1_id) {
            $nextMatch->update(['participant_1_id' => $winner->id]);
        } elseif (!$nextMatch->participant_2_id) {
            $nextMatch->update(['participant_2_id' => $winner->id]);
        }
    }
}
