<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Models\Team;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(protected TeamService $teamService) {}

    public function index(Request $request): JsonResponse
    {
        $teams = Team::with('captain')->paginate($request->input('per_page', 10));
        return response()->json($teams);
    }

    public function mine(Request $request): JsonResponse
    {
        $teams = $request->user()->teams()->with('captain')->get();
        return response()->json($teams);
    }

    public function store(CreateTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->createTeam($request->validated(), $request->user());
        return response()->json($team, 201);
    }

    public function show(Team $team): JsonResponse
    {
        $team->load(['members', 'captain']);
        return response()->json($team);
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $team->update($request->validated());
        return response()->json($team);
    }

    public function destroy(Team $team, Request $request): JsonResponse
    {
        if ($request->user()->id !== $team->captain_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->delete();
        return response()->json(null, 204);
    }
}
