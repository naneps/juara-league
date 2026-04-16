<?php

namespace App\Repositories\Contracts;

use App\Models\Tournament;
use Illuminate\Pagination\LengthAwarePaginator;

interface TournamentRepositoryInterface
{
    public function all(int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): ?Tournament;

    public function findBySlug(string $slug): ?Tournament;

    public function findByUserId(string $userId, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Tournament;

    public function update(Tournament $tournament, array $data): Tournament;

    public function delete(Tournament $tournament): bool;
}
