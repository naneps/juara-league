<?php

namespace App\Services;

use App\Models\Stage;
use App\Models\Tournament;
use Illuminate\Support\Collection;

class StageService
{
    public function getStagesByTournament(Tournament $tournament): Collection
    {
        return $tournament->stages;
    }

    public function createStage(Tournament $tournament, array $data): Stage
    {
        // Auto-increment order
        $lastOrder = $tournament->stages()->max('order') ?? 0;
        $data['order'] = $lastOrder + 1;
        $data['tournament_id'] = $tournament->id;

        return Stage::create($data);
    }

    public function updateStage(Stage $stage, array $data): Stage
    {
        $stage->update($data);
        return $stage;
    }

    public function deleteStage(Stage $stage): bool
    {
        return $stage->delete();
    }

    public function reorderStages(Tournament $tournament, array $orderMap): void
    {
        foreach ($orderMap as $id => $order) {
            $tournament->stages()->where('id', $id)->update(['order' => $order]);
        }
    }
}
