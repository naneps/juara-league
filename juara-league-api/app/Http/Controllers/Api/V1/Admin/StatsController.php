<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Get platform statistics for admin dashboard.
     */
    public function index(): JsonResponse
    {
        // 1. Overview Stats
        $overview = [
            'users' => [
                'total' => User::count(),
                'growth' => $this->getGrowth(User::class),
                'verified' => User::whereNotNull('email_verified_at')->count(),
            ],
            'tournaments' => [
                'total' => Tournament::count(),
                'pending' => Tournament::where('approval_status', 'pending_review')->count(),
                'active' => Tournament::where('status', 'ongoing')->count(),
            ],
            'teams' => [
                'total' => Team::count(),
            ]
        ];

        // 2. Recent Activities
        $activities = Tournament::with('user', 'sport')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'type' => 'tournament_created',
                    'user' => $t->user->name,
                    'title' => $t->title,
                    'time' => $t->created_at->diffForHumans(),
                ];
            });

        // 3. Sport Distribution
        $distribution = DB::table('tournaments')
            ->join('sports', 'tournaments.sport_id', '=', 'sports.id')
            ->select('sports.name', DB::raw('count(*) as count'))
            ->groupBy('sports.name')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'overview' => $overview,
            'activities' => $activities,
            'distribution' => $distribution
        ]);
    }

    /**
     * Calculate growth percentage for the last 30 days vs previous 30 days.
     */
    private function getGrowth(string $model): float
    {
        $current = $model::where('created_at', '>=', now()->subDays(30))->count();
        $previous = $model::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
