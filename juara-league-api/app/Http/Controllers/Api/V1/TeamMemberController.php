<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\InviteMemberRequest;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function __construct(protected TeamService $teamService) {}

    public function invite(InviteMemberRequest $request, Team $team): JsonResponse
    {
        try {
            $invitation = $this->teamService->createInvitation($team, $request->get('email'));
            return response()->json([
                'message' => 'Invitation sent.',
                'invitation' => $invitation,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        try {
            $team = $this->teamService->acceptInvitation($token, $request->user());
            return response()->json([
                'message' => 'Successfully joined the team.',
                'team' => $team,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function decline(Request $request, string $token): JsonResponse
    {
        try {
            $this->teamService->declineInvitation($token, $request->user());
            return response()->json(['message' => 'Invitation declined.']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function remove(Request $request, Team $team, User $user): JsonResponse
    {
        // Only captain can remove others, or user can leave themselves
        if ($request->user()->id !== $team->captain_id && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $this->teamService->removeMember($team, $user);
            return response()->json(['message' => 'Member removed successfully.']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function transfer(Request $request, Team $team): JsonResponse
    {
        if ($request->user()->id !== $team->captain_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $newCaptainId = $request->input('user_id');
        $newCaptain = User::findOrFail($newCaptainId);

        try {
            $this->teamService->transferCaptaincy($team, $newCaptain);
            return response()->json(['message' => 'Captaincy transferred successfully.']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
