<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\User;
use App\Repositories\Contracts\TournamentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TournamentService
{
    public function __construct(
        protected TournamentRepositoryInterface $tournamentRepository
    ) {}

    public function getAllTournaments(int $perPage = 15): LengthAwarePaginator
    {
        return $this->tournamentRepository->all($perPage);
    }

    public function getTournamentBySlug(string $slug): ?Tournament
    {
        return $this->tournamentRepository->findBySlug($slug);
    }

    public function getUserTournaments(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->tournamentRepository->findByUserId($userId, $perPage);
    }

    public function createTournament(User $user, array $data): Tournament
    {
        $data['user_id'] = $user->id;
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['status'] = $data['status'] ?? 'draft';

        return $this->tournamentRepository->create($data);
    }

    public function updateTournament(Tournament $tournament, array $data): Tournament
    {
        if (isset($data['title']) && $data['title'] !== $tournament->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        return $this->tournamentRepository->update($tournament, $data);
    }

    public function deleteTournament(Tournament $tournament): bool
    {
        return $this->tournamentRepository->delete($tournament);
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
