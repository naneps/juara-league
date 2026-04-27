<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\Participant;
use App\Models\TournamentMatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get organizer dashboard overview stats and data.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();

        // 1. Counter Stats
        $tournaments = Tournament::where('user_id', $user->id);
        $totalTournaments = (clone $tournaments)->count();
        $ongoingTournaments = (clone $tournaments)->where('status', 'ongoing')->count();
        
        // Participants pending approval for tournaments owned by this user
        $pendingParticipantsCount = Participant::whereHas('tournament', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'pending')->count();

        // Matches scheduled for today in user's tournaments
        $matchesTodayCount = TournamentMatch::whereHas('stage.tournament', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->whereDate('scheduled_at', Carbon::today())
        ->count();

        // 2. Recent Pending Participants (Latest 5)
        $recentParticipants = Participant::with(['user', 'team', 'tournament'])
            ->whereHas('tournament', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        // 3. Upcoming Matches (Next 48 hours)
        $upcomingMatches = TournamentMatch::with([
                'stage.tournament', 
                'matchParticipants'
            ])
            ->whereHas('stage.tournament', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('status', 'upcoming')
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        // 4. Quick Tournament List (Latest 5)
        $recentTournaments = (clone $tournaments)
            ->withCount('participants')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_tournaments' => $totalTournaments,
                'ongoing_tournaments' => $ongoingTournaments,
                'pending_participants' => $pendingParticipantsCount,
                'matches_today' => $matchesTodayCount,
            ],
            'recent_participants' => \App\Http\Resources\ParticipantResource::collection($recentParticipants),
            'upcoming_matches' => \App\Http\Resources\TournamentMatchResource::collection($upcomingMatches),
            'recent_tournaments' => \App\Http\Resources\TournamentResource::collection($recentTournaments),
        ]);
    }
}
