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
        $group->load('matches.matchParticipants.participant.user', 'matches.matchParticipants.participant.team');
        $matches = $group->matches;
        
        $standings = [];

        // Initialize standings for all participants in the group
        foreach ($matches as $match) {
            foreach ($match->matchParticipants as $mp) {
                if (!isset($standings[$mp->participant_id])) {
                    $standings[$mp->participant_id] = $this->initializeStanding($mp->participant);
                }
            }
        }

        // Calculate points and stats from completed matches
        foreach ($matches as $match) {
            if ($match->status !== 'completed' && $match->status !== 'bye') {
                continue;
            }

            if ($match->status === 'bye') {
                continue;
            }

            $participants = $match->matchParticipants()->orderBy('slot')->get();
            if ($participants->count() < 2) {
                continue;
            }

            $p1 = $participants[0];
            $p2 = $participants[1];

            $standings[$p1->participant_id]['played']++;
            $standings[$p2->participant_id]['played']++;

            $scoreP1 = $match->scores['participant_1'] ?? 0;
            $scoreP2 = $match->scores['participant_2'] ?? 0;

            $standings[$p1->participant_id]['goals_for'] += $scoreP1;
            $standings[$p1->participant_id]['goals_against'] += $scoreP2;

            $standings[$p2->participant_id]['goals_for'] += $scoreP2;
            $standings[$p2->participant_id]['goals_against'] += $scoreP1;

            if ($match->winner_id === $p1->participant_id) {
                $standings[$p1->participant_id]['win']++;
                $standings[$p1->participant_id]['points'] += $pointsConfig['win'];
                $standings[$p2->participant_id]['loss']++;
                $standings[$p2->participant_id]['points'] += $pointsConfig['loss'];
            } elseif ($match->winner_id === $p2->participant_id) {
                $standings[$p2->participant_id]['win']++;
                $standings[$p2->participant_id]['points'] += $pointsConfig['win'];
                $standings[$p1->participant_id]['loss']++;
                $standings[$p1->participant_id]['points'] += $pointsConfig['loss'];
            } else {
                $standings[$p1->participant_id]['draw']++;
                $standings[$p1->participant_id]['points'] += $pointsConfig['draw'];
                $standings[$p2->participant_id]['draw']++;
                $standings[$p2->participant_id]['points'] += $pointsConfig['draw'];
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
