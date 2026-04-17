<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\TournamentMatchResource;
use App\Models\Game;
use App\Models\Stage;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\MatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function __construct(
        protected MatchService $matchService
    ) {}

    /**
     * List all matches in a stage (public).
     */
    public function index(Request $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $matches = $this->matchService->getMatchesByStage($stage, [
            'status' => $request->query('status'),
            'round' => $request->query('round'),
            'participant_id' => $request->query('participant_id'),
        ]);

        return response()->json([
            'data' => TournamentMatchResource::collection($matches),
        ]);
    }

    /**
     * Get match detail with games (public).
     */
    public function show(Tournament $tournament, Stage $stage, TournamentMatch $match): JsonResponse
    {
        if ($stage->tournament_id !== $tournament->id || $match->stage_id !== $stage->id) {
            return response()->json(['message' => 'Match tidak ditemukan.'], 404);
        }

        $match = $this->matchService->getMatchDetail($match);

        return response()->json([
            'data' => new TournamentMatchResource($match),
        ]);
    }

    /**
     * Update match status or schedule (Staff only).
     */
    public function update(Request $request, Tournament $tournament, Stage $stage, TournamentMatch $match): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id || $match->stage_id !== $stage->id) {
            return response()->json(['message' => 'Match tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|string|in:ongoing',
            'scheduled_at' => 'sometimes|date|after:now',
        ]);

        $match = $this->matchService->updateMatch($match, $validated);

        return response()->json([
            'message' => 'Match berhasil diupdate.',
            'data' => new TournamentMatchResource($match),
        ]);
    }

    /**
     * Input a game result (Staff only).
     */
    public function storeGame(Request $request, Tournament $tournament, Stage $stage, TournamentMatch $match): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id || $match->stage_id !== $stage->id) {
            return response()->json(['message' => 'Match tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'game_number' => 'required|integer|min:1',
            'winner_id' => 'required|string',
            'score_p1' => 'nullable|integer|min:0',
            'score_p2' => 'nullable|integer|min:0',
        ]);

        $result = $this->matchService->inputGameResult($match, $validated);

        $message = $result['match_status'] === 'completed'
            ? "Hasil game {$validated['game_number']} diinput. Match selesai!"
            : "Hasil game {$validated['game_number']} berhasil diinput.";

        return response()->json([
            'message' => $message,
            'data' => [
                'game' => new GameResource($result['game']),
                'match_status' => $result['match_status'],
                'current_score' => $result['current_score'],
                'match_winner' => $result['match_winner'],
                'next_match_id' => $result['next_match_id'],
            ],
        ], 201);
    }

    /**
     * Correct a game result (Owner only).
     */
    public function updateGame(Request $request, Tournament $tournament, Stage $stage, TournamentMatch $match, Game $game): JsonResponse
    {
        if ($request->user()->id !== $tournament->user_id) {
            return response()->json(['message' => 'Hanya Owner yang dapat mengoreksi hasil.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id || $match->stage_id !== $stage->id || $game->match_id !== $match->id) {
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'winner_id' => 'required|string',
            'score_p1' => 'nullable|integer|min:0',
            'score_p2' => 'nullable|integer|min:0',
        ]);

        // Validate winner is one of the participants
        if ($validated['winner_id'] !== $match->participant_1_id && $validated['winner_id'] !== $match->participant_2_id) {
            return response()->json(['message' => 'Pemenang harus salah satu peserta match.'], 422);
        }

        $result = $this->matchService->correctGameResult($game, $validated);

        return response()->json([
            'message' => 'Hasil game berhasil dikoreksi. Bracket telah diupdate.',
            'data' => [
                'game' => new GameResource($result['game']),
                'match_winner' => $result['match_winner'],
            ],
        ]);
    }

    /**
     * Check whether the request user is a staff member of the tournament.
     */
    protected function isStaff(Request $request, Tournament $tournament): bool
    {
        $user = $request->user();

        if ($user->id === $tournament->user_id) {
            return true;
        }

        return $tournament->staff()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'co_organizer', 'referee'])
            ->exists();
    }
}
