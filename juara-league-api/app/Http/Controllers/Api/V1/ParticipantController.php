<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Tournament;
use App\Services\ParticipantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function __construct(
        protected ParticipantService $participantService
    ) {}

    public function index(Tournament $tournament): JsonResponse
    {
        return response()->json([
            'data' => $this->participantService->getTournamentParticipants($tournament)
        ]);
    }

    public function store(Request $request, Tournament $tournament): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'team_id' => 'required_without:user_id|exists:teams,id',
            'payment_proof_url' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Default to current user if no user_id or team_id provided (for Individual)
        if (!isset($validated['user_id']) && !isset($validated['team_id'])) {
            $validated['user_id'] = $request->user()->id;
        }

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
