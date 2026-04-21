<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Support\Collection;

class GroupService
{
    /**
     * Calculate standings for a group based on match results.
     *
     * @param Group $group
     * @param array $pointsConfig Array defining points for win, draw, loss
     * @return Collection Collection of participants sorted by their standing
     */
    public function calculateGroupStandings(Group $group, array $pointsConfig = ['win' => 3, 'draw' => 1, 'loss' => 0]): Collection
    {
        $group->load('matches.participant1.user', 'matches.participant1.team', 'matches.participant2.user', 'matches.participant2.team');
        $matches = $group->matches;
        
        $standings = [];

        // Initialize standings for all participants in the group
        foreach ($matches as $match) {
            if ($match->participant_1_id && !isset($standings[$match->participant_1_id])) {
                $standings[$match->participant_1_id] = $this->initializeStanding($match->participant1);
            }
            if ($match->participant_2_id && !isset($standings[$match->participant_2_id])) {
                $standings[$match->participant_2_id] = $this->initializeStanding($match->participant2);
            }
        }

        // Calculate points and stats from completed matches
        foreach ($matches as $match) {
            if ($match->status !== 'completed' && $match->status !== 'bye') {
                continue; // Only process finished matches
            }

            if ($match->status === 'bye') {
                // If it's a bye match, you could handle it here or skip. Usually skipped for scoring.
                continue;
            }

            $p1Id = $match->participant_1_id;
            $p2Id = $match->participant_2_id;

            if (!$p1Id || !$p2Id) {
                continue; // Skip if participants are incomplete
            }

            $standings[$p1Id]['played']++;
            $standings[$p2Id]['played']++;

            $scoreP1 = $match->scores['participant_1'] ?? 0;
            $scoreP2 = $match->scores['participant_2'] ?? 0;

            // Add Goals/Scores (GF = Goals For, GA = Goals Against)
            $standings[$p1Id]['goals_for'] += $scoreP1;
            $standings[$p1Id]['goals_against'] += $scoreP2;

            $standings[$p2Id]['goals_for'] += $scoreP2;
            $standings[$p2Id]['goals_against'] += $scoreP1;

            if ($match->winner_id === $p1Id) {
                // P1 Wins
                $standings[$p1Id]['win']++;
                $standings[$p1Id]['points'] += $pointsConfig['win'];
                
                $standings[$p2Id]['loss']++;
                $standings[$p2Id]['points'] += $pointsConfig['loss'];
            } elseif ($match->winner_id === $p2Id) {
                // P2 Wins
                $standings[$p2Id]['win']++;
                $standings[$p2Id]['points'] += $pointsConfig['win'];
                
                $standings[$p1Id]['loss']++;
                $standings[$p1Id]['points'] += $pointsConfig['loss'];
            } else {
                // Draw (If winner is null but match is completed, it's a draw)
                $standings[$p1Id]['draw']++;
                $standings[$p1Id]['points'] += $pointsConfig['draw'];
                
                $standings[$p2Id]['draw']++;
                $standings[$p2Id]['points'] += $pointsConfig['draw'];
            }
        }

        // Calculate Goal Difference
        foreach ($standings as $pid => &$stat) {
            $stat['goal_difference'] = $stat['goals_for'] - $stat['goals_against'];
        }

        // Convert to collection and sort
        $collection = collect(array_values($standings));

        return $collection->sort(function ($a, $b) {
            // Sort by Points DESC
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            // Sort by Goal Difference DESC
            if ($a['goal_difference'] !== $b['goal_difference']) {
                return $b['goal_difference'] <=> $a['goal_difference'];
            }
            // Sort by Goals For DESC
            return $b['goals_for'] <=> $a['goals_for'];
        })->values();
    }

    /**
     * Initialize empty standing record for a participant.
     */
    private function initializeStanding($participant): array
    {
        return [
            'participant_id' => $participant->id,
            'participant' => $participant,
            'played' => 0,
            'win' => 0,
            'draw' => 0,
            'loss' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
        ];
    }
}
