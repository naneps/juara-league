<?php

namespace App\Repositories\Contracts;

use App\Models\Stage;
use Illuminate\Support\Collection;

interface StageRepositoryInterface
{
    /**
     * Get all stages for a tournament.
     */
    public function allByTournamentId(string $tournamentId): Collection;

    /**
     * Find a stage by ID.
     */
    public function findById(string $id): ?Stage;

    /**
     * Create a new stage.
     */
    public function create(array $data): Stage;

    /**
     * Update an existing stage.
     */
    public function update(Stage $stage, array $data): Stage;

    /**
     * Delete a stage.
     */
    public function delete(Stage $stage): bool;

    /**
     * Get the maximum order for a tournament's stages.
     */
    public function getMaxOrder(string $tournamentId): int;
}
