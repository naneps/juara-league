<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdvanceParticipantsRequest;
use App\Http\Requests\Api\CreateStageRequest;
use App\Http\Requests\Api\SeedParticipantsRequest;
use App\Http\Resources\StageResource;
use App\Models\Stage;
use App\Models\Tournament;
use App\Services\StageService;
use App\Services\MatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(
        protected StageService $stageService,
        protected MatchService $matchService
    ) {}

    /**
     * List all stages for a tournament (public).
     */
    public function index(Tournament $tournament): JsonResponse
    {
        $stages = $this->stageService->getStagesByTournament($tournament);

        return response()->json([
            'data' => StageResource::collection($stages),
        ]);
    }

    /**
     * Get stage detail with bracket/standing (public).
     */
    public function show(Tournament $tournament, Stage $stage): JsonResponse
    {
        // Verify stage belongs to tournament
        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $stage = $this->stageService->getStageDetail($stage);

        return response()->json([
            'data' => new StageResource($stage),
        ]);
    }

    /**
     * Create a new stage for a tournament.
     */
    public function store(CreateStageRequest $request, Tournament $tournament): JsonResponse
    {
        // Authorization: Owner or Co-Organizer
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        $stage = $this->stageService->createStage($tournament, $request->validated());

        // Include format recommendation
        $participantCount = $tournament->participants()->where('status', 'approved')->count();
        $recommendation = $this->stageService->getFormatRecommendation($participantCount);

        return response()->json([
            'message' => 'Stage berhasil ditambahkan.',
            'data' => new StageResource($stage),
            'recommendation' => $recommendation ?: null,
        ], 201);
    }

    /**
     * Update a stage configuration.
     */
    public function update(Request $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|min:2|max:100',
            'type' => 'sometimes|string|in:single_elim,double_elim,round_robin,swiss',
            'bo_format' => 'sometimes|string|in:bo1,bo3,bo5,bo7',
            'participants_advance' => 'sometimes|integer|min:1',
            'groups_count' => 'nullable|integer|min:1',
            'participants_per_group' => 'nullable|integer|min:2',
            'settings' => 'nullable|array',
        ]);

        $stage = $this->stageService->updateStage($stage, $validated);

        return response()->json([
            'message' => 'Stage berhasil diperbarui.',
            'data' => new StageResource($stage),
        ]);
    }

    /**
     * Delete a stage.
     */
    public function destroy(Request $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if ($request->user()->id !== $tournament->user_id) {
            return response()->json(['message' => 'Hanya Owner yang dapat menghapus stage.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $this->stageService->deleteStage($stage);

        return response()->json(['message' => 'Stage berhasil dihapus.']);
    }

    /**
     * Set seeding for participants before stage starts.
     */
    public function seed(SeedParticipantsRequest $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $this->stageService->seedParticipants($stage, $request->validated()['seeds']);

        return response()->json([
            'message' => 'Seeding berhasil disimpan.',
        ]);
    }

    /**
     * Randomly shuffle participants in a stage.
     */
    public function shuffle(Request $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $this->stageService->shuffleParticipants($stage);

        return response()->json([
            'message' => 'Peserta berhasil diacak.',
        ]);
    }

    /**
     * Start a stage and generate bracket.
     */
    public function start(Request $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $result = $this->stageService->startStage($stage);

        return response()->json([
            'message' => 'Stage berhasil dimulai. Bracket telah digenerate.',
            'data' => $result,
        ]);
    }

    /**
     * Reset a stage and delete generated bracket.
     */
    public function reset(Request $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $this->stageService->resetStage($stage);

        return response()->json([
            'message' => 'Stage berhasil direset. Bracket telah dihapus.',
        ]);
    }

    /**
     * Advance participants to the next stage.
     */
    public function advance(AdvanceParticipantsRequest $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $result = $this->stageService->advanceParticipants(
            $stage,
            $request->validated()['advancing_participants']
        );

        return response()->json([
            'message' => count($request->validated()['advancing_participants']) . ' peserta berhasil diadvance ke stage berikutnya.',
            'data' => $result,
        ]);
    }

    /**
     * Get standings for a stage (Swiss or Round Robin without groups).
     */
    public function standings(Tournament $tournament, Stage $stage): JsonResponse
    {
        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $standings = $this->stageService->getStageStandings($stage);

        return response()->json([
            'data' => $standings,
        ]);
    }

    /**
     * Auto-schedule matches in a stage.
     */
    public function autoSchedule(Request $request, Tournament $tournament, Stage $stage): JsonResponse
    {
        if (!$this->isStaff($request, $tournament)) {
            return response()->json(['message' => 'Hanya staff turnamen yang dapat melakukan ini.'], 403);
        }

        if ($stage->tournament_id !== $tournament->id) {
            return response()->json(['message' => 'Stage tidak ditemukan di turnamen ini.'], 404);
        }

        $settings = $request->validate([
            'start_at' => 'nullable|date',
            'interval_minutes' => 'nullable|integer|min:15|max:1440',
            'matches_per_day' => 'nullable|integer|min:1|max:100',
        ]);

        $count = $this->matchService->autoScheduleMatches($stage, $settings);

        return response()->json([
            'message' => "{$count} pertandingan berhasil dijadwalkan secara otomatis.",
        ]);
    }

    /**
     * Check whether the request user is a staff member (owner or co-organizer) of the tournament.
     */
    protected function isStaff(Request $request, Tournament $tournament): bool
    {
        $user = $request->user();

        // Owner is always staff
        if ($user->id === $tournament->user_id) {
            return true;
        }

        // Check tournament_staff table
        return $tournament->staff()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'co_organizer'])
            ->exists();
    }
}
