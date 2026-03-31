<?php

namespace App\Services;

use App\Models\Sport;
use App\Models\Tournament;
use App\Repositories\Contracts\SportRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SportService
{
    /**
     * @param SportRepositoryInterface $sportRepository
     */
    public function __construct(
        protected SportRepositoryInterface $sportRepository
    ) {}

    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllSports(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        return $this->sportRepository->getAll($filters, $perPage);
    }

    /**
     * @param string $id
     * @return Sport|null
     */
    public function getSportById(string $id): ?Sport
    {
        return $this->sportRepository->findById($id);
    }

    /**
     * @param array $data
     * @return Sport
     */
    public function createSport(array $data): Sport
    {
        return $this->sportRepository->create($data);
    }

    /**
     * @param Sport $sport
     * @param array $data
     * @return Sport
     */
    public function updateSport(Sport $sport, array $data): Sport
    {
        // SRS: Sport yang sudah digunakan oleh turnamen aktif tidak dapat diubah tipenya
        if (isset($data['type']) && $data['type'] !== $sport->type) {
            if ($this->isSportUsed($sport)) {
                throw ValidationException::withMessages([
                    'type' => ['Cannot change sport type because it is already used in a tournament.'],
                ]);
            }
        }

        return $this->sportRepository->update($sport, $data);
    }

    /**
     * @param Sport $sport
     * @return bool
     * @throws ValidationException
     */
    public function deleteSport(Sport $sport): bool
    {
        // SRS: Sport yang sudah digunakan oleh minimal satu turnamen tidak dapat dihapus
        if ($this->isSportUsed($sport)) {
            throw ValidationException::withMessages([
                'sport' => ['Cannot delete sport because it is already used in a tournament.'],
            ]);
        }

        return $this->sportRepository->delete($sport);
    }

    /**
     * Check if a sport is used in any tournament.
     * 
     * @param Sport $sport
     * @return bool
     */
    protected function isSportUsed(Sport $sport): bool
    {
        // Note: We'll assume the tournament table will have a 'sport_id' column
        // If the column doesn't exist yet, this will fail.
        // We should add the column to 'tournaments' table in this module or the next.
        try {
            return Tournament::where('sport_id', $sport->id)->exists();
        } catch (\Exception $e) {
            // If column doesn't exist, it's not used yet
            return false;
        }
    }
}
