<?php

namespace App\Repositories\Eloquent;

use App\Models\Sport;
use App\Repositories\Contracts\SportRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SportRepository implements SportRepositoryInterface
{
    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
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

    /**
     * @param string $id
     * @return Sport|null
     */
    public function findById(string $id): ?Sport
    {
        return Sport::find($id);
    }

    /**
     * @param array $data
     * @return Sport
     */
    public function create(array $data): Sport
    {
        return Sport::create($data);
    }

    /**
     * @param Sport $sport
     * @param array $data
     * @return Sport
     */
    public function update(Sport $sport, array $data): Sport
    {
        $sport->update($data);
        return $sport;
    }

    /**
     * @param Sport $sport
     * @return bool
     */
    public function delete(Sport $sport): bool
    {
        return $sport->delete();
    }
}
