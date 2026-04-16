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

    public function getTournamentBySlug(string $slug): ?Tournament
    {
        return $this->tournamentRepository->findBySlug($slug);
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
}
