<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentStaffResource;
use App\Models\Tournament;
use App\Models\User;
use App\Services\TournamentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentStaffController extends Controller
{
    public function __construct(
        protected TournamentService $tournamentService
    ) {}

    /**
     * Display a listing of staff for a tournament.
     */
    public function index(Tournament $tournament): JsonResponse
    {
        $staff = $tournament->staff()->with('user')->get();
        return response()->json([
            'data' => TournamentStaffResource::collection($staff)
        ]);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request, Tournament $tournament): JsonResponse
    {
        // Simple authorization: only owner can manage staff
        if ($request->user()->id !== $tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:co_organizer,referee',
        ]);

        $user = User::where('email', $request->email)->first();

        try {
            $this->tournamentService->addStaff($tournament, $user->id, $request->role);
            return response()->json(['message' => 'Staff added successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(Request $request, Tournament $tournament, User $user): JsonResponse
    {
        // Simple authorization: only owner can manage staff
        if ($request->user()->id !== $tournament->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $deleted = $this->tournamentService->removeStaff($tournament, $user->id);

        if ($deleted) {
            return response()->json(['message' => 'Staff removed successfully.']);
        }

        return response()->json(['message' => 'Staff member not found.'], 404);
    }
}
