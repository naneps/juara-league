<?php

namespace App\Repositories\Contracts;

use App\Models\Sport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SportRepositoryInterface
{
    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 50): LengthAwarePaginator;

    /**
     * @param string $id
     * @return Sport|null
     */
    public function findById(string $id): ?Sport;

    /**
     * @param array $data
     * @return Sport
     */
    public function create(array $data): Sport;

    /**
     * @param Sport $sport
     * @param array $data
     * @return Sport
     */
    public function update(Sport $sport, array $data): Sport;

    /**
     * @param Sport $sport
     * @return bool
     */
    public function delete(Sport $sport): bool;
}
