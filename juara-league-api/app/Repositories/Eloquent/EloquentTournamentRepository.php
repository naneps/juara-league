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

    public function findById(string $id): ?Tournament
    {
        return Tournament::find($id);
    }

    public function findBySlug(string $slug, array $includes = []): ?Tournament
    {
        $defaultIncludes = ['user', 'sport'];
        $relations = array_unique(array_merge($defaultIncludes, $includes));
        
        return Tournament::with($relations)->where('slug', $slug)->first();
    }

    public function findByUserId(string $userId, int $perPage = 15): LengthAwarePaginator
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
