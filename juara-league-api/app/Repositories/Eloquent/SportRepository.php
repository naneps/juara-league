<?php

namespace App\Repositories\Eloquent;

use App\Models\Sport;
use App\Repositories\Contracts\SportRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SportRepository implements SportRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = Sport::query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }

    public function findById(string $id): ?Sport
    {
        return Sport::find($id);
    }

    public function create(array $data): Sport
    {
        return Sport::create($data);
    }

    public function update(Sport $sport, array $data): Sport
    {
        $sport->update($data);
        return $sport;
    }

    public function delete(Sport $sport): bool
    {
        return $sport->delete();
    }
}
