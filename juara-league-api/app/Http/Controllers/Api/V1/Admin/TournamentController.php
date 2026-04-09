<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
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
    public function approve(string $id): JsonResponse
    {
        $tournament = Tournament::findOrFail($id);
        
        $tournament->update([
            'approval_status' => 'approved'
        ]);

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
        
        $tournament->update([
            'approval_status' => 'rejected',
            // Kita bisa tambahkan kolom notes/reason nanti jika diperlukan
            // Untuk sekarang kita update status saja
        ]);

        return response()->json([
            'message' => 'Turnamen telah ditolak.',
            'data' => new TournamentResource($tournament)
        ]);
    }
}
