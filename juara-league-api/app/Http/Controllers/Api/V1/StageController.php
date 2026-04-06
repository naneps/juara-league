<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Tournament;
use App\Services\StageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(
        protected StageService $stageService
    ) {}

    public function index(Tournament $tournament): JsonResponse
    {
        return response()->json([
            'data' => $this->stageService->getStagesByTournament($tournament)
        ]);
    }

    public function store(Request $request, Tournament $tournament): JsonResponse
    {
        // Authorization: Only owner can add stages
        if ($request->user()->id !== $tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:single_elim,double_elim,round_robin,swiss',
            'settings' => 'nullable|array',
        ]);

        $stage = $this->stageService->createStage($tournament, $validated);

        return response()->json([
            'data' => $stage,
            'message' => 'Stage created successfully.'
        ], 201);
    }

    public function update(Request $request, Stage $stage): JsonResponse
    {
        // Authorization: Only owner of the tournament can update its stages
        if ($request->user()->id !== $stage->tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:single_elim,double_elim,round_robin,swiss',
            'settings' => 'nullable|array',
            'order' => 'sometimes|integer',
        ]);

        $updatedStage = $this->stageService->updateStage($stage, $validated);

        return response()->json([
            'data' => $updatedStage,
            'message' => 'Stage updated successfully.'
        ]);
    }

    public function destroy(Request $request, Stage $stage): JsonResponse
    {
        if ($request->user()->id !== $stage->tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $this->stageService->deleteStage($stage);

        return response()->json(['message' => 'Stage deleted successfully.']);
    }
}
