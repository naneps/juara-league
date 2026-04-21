<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StandingResource;
use App\Models\Group;
use App\Models\Stage;
use App\Models\Tournament;
use App\Services\GroupService;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    public function __construct(protected GroupService $groupService) {}

    /**
     * Get the standings for a specific group.
     */
    public function standings(Tournament $tournament, Stage $stage, Group $group): JsonResponse
    {
        // Validation checks to ensure hierarchies are correct
        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        if ($group->stage_id !== $stage->id) {
            return response()->json(['message' => 'Grup tidak ditemukan di stage ini.'], 404);
        }

        // Get points configuration from stage settings if available, else default
        $pointsConfig = $stage->settings['pointsConfig'] ?? ['win' => 3, 'draw' => 1, 'loss' => 0];

        $standings = $this->groupService->calculateGroupStandings($group, $pointsConfig);

        return response()->json([
            'data' => StandingResource::collection($standings),
        ]);
    }
}
