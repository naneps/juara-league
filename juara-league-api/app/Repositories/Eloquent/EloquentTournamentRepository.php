<?php

namespace App\Repositories\Eloquent;

use App\Models\Tournament;
use App\Repositories\Contracts\TournamentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentTournamentRepository implements TournamentRepositoryInterface
{
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return Tournament::latest()->paginate($perPage);
    }

    public function findById(int $id): ?Tournament
    {
        return Tournament::find($id);
    }

    public function findBySlug(string $slug): ?Tournament
    {
        return Tournament::with(['user', 'sport'])->where('slug', $slug)->first();
    }

    public function findByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Tournament::where('user_id', $userId)->latest()->paginate($perPage);
    }

    public function create(array $data): Tournament
    {
        return Tournament::create($data);
    }

    public function update(Tournament $tournament, array $data): Tournament
    {
        $tournament->update($data);
        return $tournament;
    }

    public function delete(Tournament $tournament): bool
    {
        return $tournament->delete();
    }
}
