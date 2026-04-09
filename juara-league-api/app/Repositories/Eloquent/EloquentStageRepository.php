<?php

namespace App\Repositories\Eloquent;

use App\Models\Stage;
use App\Repositories\Contracts\StageRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentStageRepository implements StageRepositoryInterface
{
    public function allByTournamentId(string $tournamentId): Collection
    {
        return Stage::where('tournament_id', $tournamentId)->orderBy('order')->get();
    }

    public function findById(string $id): ?Stage
    {
        return Stage::find($id);
    }

    public function create(array $data): Stage
    {
        return Stage::create($data);
    }

    public function update(Stage $stage, array $data): Stage
    {
        $stage->update($data);
        return $stage;
    }

    public function delete(Stage $stage): bool
    {
        return $stage->delete();
    }

    public function getMaxOrder(string $tournamentId): int
    {
        return Stage::where('tournament_id', $tournamentId)->max('order') ?? 0;
    }
}
