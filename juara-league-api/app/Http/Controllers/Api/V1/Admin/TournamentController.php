<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function __construct(
        protected \App\Services\TournamentApprovalService $approvalService
    ) {}

    /**
     * Display a listing of all tournaments for moderation.
     */
    public function index(Request $request)
    {
        $query = Tournament::with(['user', 'sport']);

        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tournaments = $query->latest()->paginate($request->query('per_page', 15));

        return TournamentResource::collection($tournaments);
    }

    /**
     * Approve a tournament.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $tournament = Tournament::findOrFail($id);
        
        $this->approvalService->approveManually($tournament, $request->user()->id, $request->note);

        return response()->json([
            'message' => 'Turnamen berhasil disetujui.',
            'data' => new TournamentResource($tournament)
        ]);
    }

    /**
     * Reject a tournament.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $tournament = Tournament::findOrFail($id);
        
        $this->approvalService->rejectManually($tournament, $request->user()->id, $request->reason);

        return response()->json([
            'message' => 'Turnamen telah ditolak.',
            'data' => new TournamentResource($tournament)
        ]);
    }
}
