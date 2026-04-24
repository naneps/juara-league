<?php

namespace App\Services\BracketGenerators;

use App\Models\Group;
use App\Models\Participant;
use App\Models\Stage;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;

class RoundRobinGenerator
{
    /**
     * Generate round robin schedule for the given stage.
     *
     * @param Stage $stage
     * @param Collection<Participant> $participants Already seeded/ordered
     * @return int Number of matches generated
     */
    public function generate(Stage $stage, Collection $participants): int
    {
        $groupsCount = $stage->groups_count ?? 1;
        $matchCount = 0;

        // Divide participants into groups
        $groups = $this->divideIntoGroups($participants, $groupsCount);

        foreach ($groups as $groupIndex => $groupParticipants) {
            // Create group record
            $group = Group::create([
                'stage_id' => $stage->id,
                'name' => 'Grup ' . chr(65 + $groupIndex), // Grup A, Grup B, etc.
                'order' => $groupIndex + 1,
            ]);

            // Generate round robin schedule within the group
            $matchCount += $this->generateGroupSchedule($stage, $group, collect($groupParticipants));
        }

        return $matchCount;
    }

    /**
     * Divide participants into groups using serpentine/snake seeding.
     * Example with 8 participants into 2 groups:
     *   Group A: Seed 1, Seed 4, Seed 5, Seed 8
     *   Group B: Seed 2, Seed 3, Seed 6, Seed 7
     */
    protected function divideIntoGroups(Collection $participants, int $groupsCount): array
    {
        $groups = array_fill(0, $groupsCount, []);
        $direction = 1; // 1 = forward, -1 = backward (snake draft)
        $groupIndex = 0;

        foreach ($participants as $participant) {
            $groups[$groupIndex][] = $participant;

            // Snake draft: go forward then backward
            $groupIndex += $direction;
            if ($groupIndex >= $groupsCount) {
                $groupIndex = $groupsCount - 1;
                $direction = -1;
            } elseif ($groupIndex < 0) {
                $groupIndex = 0;
                $direction = 1;
            }
        }

        return $groups;
    }

    /**
     * Generate a complete round-robin schedule for a group.
     * Uses the circle method (rotating table) for optimal scheduling.
     */
    protected function generateGroupSchedule(Stage $stage, Group $group, Collection $participants): int
    {
        $count = $participants->count();
        $matchCount = 0;

        if ($count < 2) {
            return 0;
        }

        // If odd number of participants, add a null (bye placeholder)
        $players = $participants->values()->all();
        $hasBye = false;
        if ($count % 2 !== 0) {
            $players[] = null;
            $count++;
            $hasBye = true;
        }

        $totalRounds = $count - 1;
        $half = $count / 2;

        // Circle method: fix first player, rotate others
        for ($round = 0; $round < $totalRounds; $round++) {
            for ($i = 0; $i < $half; $i++) {
                $home = $this->getPlayerAtPosition($players, $round, $i, $count);
                $away = $this->getPlayerAtPosition($players, $round, $count - 1 - $i, $count);

                // Skip bye matches (where one player is null)
                if ($home === null || $away === null) {
                    continue;
                }

                $matchCount++;
                $match = TournamentMatch::create([
                    'stage_id' => $stage->id,
                    'group_id' => $group->id,
                    'round' => $round + 1,
                    'match_number' => $matchCount,
                    'status' => 'upcoming',
                ]);

                // Add participants to match_participants
                $match->matchParticipants()->create([
                    'participant_id' => $home->id,
                    'slot' => 1
                ]);
                $match->matchParticipants()->create([
                    'participant_id' => $away->id,
                    'slot' => 2
                ]);
            }
        }

        return $matchCount;
    }

    /**
     * Get the player at a rotated position using the circle method.
     */
    protected function getPlayerAtPosition(array $players, int $round, int $position, int $count): ?Participant
    {
        if ($position === 0) {
            // First position is always fixed
            return $players[0];
        }

        // Rotate the remaining players
        $rotatedIndex = (($position - 1 + $round) % ($count - 1)) + 1;

        return $players[$rotatedIndex] ?? null;
    }
}
