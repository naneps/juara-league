<?php

namespace App\Services;

use App\Exceptions\TournamentException;
use App\Models\Tournament;
use App\Models\User;
use App\Repositories\Contracts\TournamentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TournamentService
{
    public function __construct(
        protected TournamentRepositoryInterface $tournamentRepository,
        protected TournamentApprovalService $approvalService
    ) {}

    public function getAllTournaments(int $perPage = 15): LengthAwarePaginator
    {
        return $this->tournamentRepository->all($perPage);
    }

    public function getTournamentBySlug(string $slug, array $includes = []): ?Tournament
    {
        return $this->tournamentRepository->findBySlug($slug, $includes);
    }

    public function getUserTournaments(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->tournamentRepository->findByUserId($userId, $perPage);
    }

    public function createTournament(User $user, array $data): Tournament
    {
        $data['user_id'] = $user->id;
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['status'] = 'draft'; // Always start as draft per SRS

        $tournament = $this->tournamentRepository->create($data);

        // Evaluate approval status
        $this->approvalService->evaluate($tournament);

        return $tournament;
    }

    public function updateTournament(Tournament $tournament, array $data): Tournament
    {
        if (isset($data['title']) && $data['title'] !== $tournament->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        // BR03: sport_id cannot be changed after publication
        if (isset($data['sport_id']) && $data['sport_id'] !== $tournament->sport_id) {
            if ($tournament->status !== 'draft') {
                throw new TournamentException('Sport cannot be changed after publication.', 'BR03_VIOLATION', 409);
            }
        }

        return $this->tournamentRepository->update($tournament, $data);
    }

    public function deleteTournament(Tournament $tournament): bool
    {
        // BR12: Ongoing or completed tournaments cannot be deleted
        if (in_array($tournament->status, ['ongoing', 'completed'])) {
            throw new TournamentException('Ongoing or completed tournaments cannot be deleted.', 'TOURNAMENT_ONGOING', 409);
        }

        return $this->tournamentRepository->delete($tournament);
    }

    /**
     * Publish tournament: Move from draft to registration.
     */
    public function publish(Tournament $tournament): Tournament
    {
        if ($tournament->status !== 'draft') {
            throw new TournamentException('Tournament is already published or in progress.', 'INVALID_STATUS', 409);
        }

        // BR01: Must have at least one stage
        if ($tournament->stages()->count() === 0) {
            throw new TournamentException('Tournament must have at least one stage before being published.', 'NO_STAGE_CONFIGURED', 400);
        }

        // Rule: Approval status must not be rejected or pending_review
        if (in_array($tournament->approval_status, ['rejected', 'pending_review'])) {
            $errorCode = $tournament->approval_status === 'rejected' ? 'TOURNAMENT_REJECTED' : 'TOURNAMENT_PENDING_REVIEW';
            throw new TournamentException("Tournament cannot be published while in {$tournament->approval_status} status.", $errorCode, 403);
        }

        return $this->tournamentRepository->update($tournament, ['status' => 'registration']);
    }

    /**
     * Add a staff member to the tournament.
     */
    public function addStaff(Tournament $tournament, string $userId, string $role): void
    {
        // Simple role validation
        if (!in_array($role, ['co_organizer', 'referee'])) {
            throw new \Exception('Invalid staff role.');
        }

        // BR06/BR07: Staff/Owner cannot be participants
        $isParticipant = $tournament->participants()->where('user_id', $userId)->exists();
        if ($isParticipant) {
             throw new TournamentException('A participant cannot be designated as staff for the same tournament.', 'REFEREE_IS_PARTICIPANT', 409);
        }

        // Create or update staff role
        $tournament->staff()->updateOrCreate(
            ['user_id' => $userId, 'tournament_id' => $tournament->id],
            ['role' => $role]
        );
    }

    /**
     * Remove a staff member from the tournament.
     */
    public function removeStaff(Tournament $tournament, string $userId): bool
    {
        return $tournament->staff()->where('user_id', $userId)->delete();
    }

    /**
     * Get statistics for the tournament overview.
     */
    public function getTournamentStats(Tournament $tournament): array
    {
        $matchesTotal = \App\Models\TournamentMatch::whereHas('stage', function($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->count();

        $matchesCompleted = \App\Models\TournamentMatch::whereHas('stage', function($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->where('status', 'completed')->count();

        $activeStage = $tournament->stages()->where('status', 'ongoing')->first();
        $stageProgress = 0;
        if ($activeStage) {
            $stageMatches = $activeStage->matches()->count();
            $stageCompleted = $activeStage->matches()->where('status', 'completed')->count();
            $stageProgress = $stageMatches > 0 ? round(($stageCompleted / $stageMatches) * 100) : 0;
        }

        $revenue = (float) $tournament->entry_fee * $tournament->participants()
            ->where('payment_status', 'paid')
            ->count();

        return [
            'matches_total' => $matchesTotal,
            'matches_completed' => $matchesCompleted,
            'participants_active' => $tournament->participants()->where('status', 'approved')->count(),
            'revenue' => $revenue,
            'active_stage' => $activeStage ? [
                'id' => $activeStage->id,
                'name' => $activeStage->name,
                'progress' => $stageProgress,
            ] : null,
            'completion_rate' => $matchesTotal > 0 ? round(($matchesCompleted / $matchesTotal) * 100) : 0,
        ];
    }

    protected function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while ($this->tournamentRepository->findBySlug($slug)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
    /**
     * Get all ongoing matches for tournaments owned by the user.
     */
    public function getUserOngoingMatches(int $userId)
    {
        return \App\Models\TournamentMatch::whereHas('stage.tournament', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('status', 'ongoing')
        ->with([
            'matchParticipants.participant.user',
            'matchParticipants.participant.team',
            'stage.tournament',
        ])
        ->get();
    }
}
