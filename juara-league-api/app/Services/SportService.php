<?php

namespace App\Services;

use App\Models\Sport;
use App\Models\Tournament;
use App\Repositories\Contracts\SportRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SportService
{
    public function __construct(
        protected SportRepositoryInterface $sportRepository
    ) {}

    public function getAllSports(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return $this->sportRepository->getAll($filters, $perPage);
    }

    public function getSportById(string $id): ?Sport
    {
        return $this->sportRepository->findById($id);
    }

    public function createSport(array $data): Sport
    {
        return $this->sportRepository->create($data);
    }

    public function updateSport(Sport $sport, array $data): Sport
    {
        if (isset($data['type']) && $data['type'] !== $sport->type) {
            if ($this->isSportUsed($sport)) {
                throw ValidationException::withMessages([
                    'type' => ['Cannot change sport type because it is already used in a tournament.'],
                ]);
            }
        }

        return $this->sportRepository->update($sport, $data);
    }

    public function deleteSport(Sport $sport): bool
    {
        if ($this->isSportUsed($sport)) {
            throw ValidationException::withMessages([
                'sport' => ['Cannot delete sport because it is already used in a tournament.'],
            ]);
        }

        return $this->sportRepository->delete($sport);
    }

    protected function isSportUsed(Sport $sport): bool
    {
        try {
            return Tournament::where('sport_id', $sport->id)->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}
