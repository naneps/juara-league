<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ParticipantResource;
use App\Models\Participant;
use App\Models\Tournament;
use App\Services\ParticipantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ParticipantController extends Controller
{
    public function __construct(
        protected ParticipantService $participantService
    ) {}

    /**
     * Get participations of the current user.
     */
    public function mine(Request $request): AnonymousResourceCollection
    {
        $participations = $this->participantService->getUserParticipations($request->user()->id);
        return ParticipantResource::collection($participations);
    }

    public function index(Tournament $tournament): JsonResponse
    {
        return response()->json([
            'data' => $this->participantService->getTournamentParticipants($tournament)
        ]);
    }

    public function store(Request $request, Tournament $tournament): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => 'sometimes|nullable|exists:teams,id',
            'payment_proof_url' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Always set the current user as the registrant (who submitted the form)
        $validated['user_id'] = $request->user()->id;

        // If individual tournament and no user_id provided, it's already set above
        // If team tournament, team_id comes from request, user_id tracks who registered

        try {
            $participant = $this->participantService->register($tournament, $validated);

            return response()->json([
                'data' => $participant,
                'message' => 'Registration successful.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function storeManual(Request $request, Tournament $tournament): JsonResponse
    {
        // Only tournament owner can add participants manually
        if ($request->user()->id !== $tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'team_name' => 'nullable|string|max:255',
        ]);

        try {
            $participant = $this->participantService->registerManual($tournament, $validated);

            return response()->json([
                'data' => $participant,
                'message' => 'Registration successful.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateStatus(Request $request, Participant $participant): JsonResponse
    {
        // Authorization: Only tournament owner can update status
        if ($request->user()->id !== $participant->tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,paid',
        ]);

        $updatedParticipant = $this->participantService->updateStatus($participant, $validated['status']);

        return response()->json([
            'data' => $updatedParticipant,
            'message' => 'Participant status updated successfully.'
        ]);
    }

    public function destroy(Request $request, Participant $participant): JsonResponse
    {
        if ($request->user()->id !== $participant->tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $this->participantService->deleteParticipant($participant);

        return response()->json(['message' => 'Participant removed successfully.']);
    }
}
