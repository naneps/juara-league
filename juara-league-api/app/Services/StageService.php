<?php

namespace App\Services;

use App\Models\Stage;
use App\Models\Tournament;
use App\Repositories\Contracts\StageRepositoryInterface;
use Illuminate\Support\Collection;

class StageService
{
    public function __construct(
        protected StageRepositoryInterface $stageRepository
    ) {}

    public function getStagesByTournament(Tournament $tournament): Collection
    {
        return $this->stageRepository->allByTournamentId($tournament->id);
    }

    public function createStage(Tournament $tournament, array $data): Stage
    {
        // Auto-increment order
        $lastOrder = $this->stageRepository->getMaxOrder($tournament->id);
        $data['order'] = $lastOrder + 1;
        $data['tournament_id'] = $tournament->id;

        return $this->stageRepository->create($data);
    }

    public function updateStage(Stage $stage, array $data): Stage
    {
        return $this->stageRepository->update($stage, $data);
    }

    public function deleteStage(Stage $stage): bool
    {
        return $this->stageRepository->delete($stage);
    }

    public function reorderStages(Tournament $tournament, array $orderMap): void
    {
        foreach ($orderMap as $id => $order) {
            $stage = $this->stageRepository->findById($id);
            if ($stage && $stage->tournament_id === $tournament->id) {
                $this->stageRepository->update($stage, ['order' => $order]);
            }
        }
    }
}
