<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Stage;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\BracketGenerators\DoubleEliminationGenerator;
use App\Services\BracketGenerators\RoundRobinGenerator;
use App\Services\BracketGenerators\SingleEliminationGenerator;
use App\Services\BracketGenerators\SwissGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StageService
{
    /**
     * Get all stages for a tournament.
     */
    public function getStagesByTournament(Tournament $tournament): Collection
    {
        return $tournament->stages()->orderBy('order')->get();
    }

    /**
     * Get a stage with its bracket/standing details.
     */
    public function getStageDetail(Stage $stage): Stage
    {
        $stage->load(['matches.participant1.user', 'matches.participant1.team',
                       'matches.participant2.user', 'matches.participant2.team',
                       'matches.winner', 'groups.matches']);

        return $stage;
    }

    /**
     * Create a new stage for a tournament.
     */
    public function createStage(Tournament $tournament, array $data): Stage
    {
        // Validate tournament is not completed
        if ($tournament->status === 'completed') {
            throw ValidationException::withMessages([
                'tournament' => ['Cannot add stages to a completed tournament.'],
            ]);
        }

        // Auto-increment order
        $lastOrder = $tournament->stages()->max('order') ?? 0;
        $data['order'] = $lastOrder + 1;
        $data['tournament_id'] = $tournament->id;
        $data['status'] = 'pending';

        return Stage::create($data);
    }

    /**
     * Update a stage's configuration.
     */
    public function updateStage(Stage $stage, array $data): Stage
    {
        if (!$stage->isEditable()) {
            throw ValidationException::withMessages([
                'stage' => ['Cannot edit a stage that has already started.'],
            ]);
        }

        $stage->update($data);
        return $stage;
    }

    /**
     * Delete a stage.
     */
    public function deleteStage(Stage $stage): bool
    {
        if (!$stage->isPending()) {
            throw ValidationException::withMessages([
                'stage' => ['Cannot delete a stage that has already started or completed.'],
            ]);
        }

        return $stage->delete();
    }

    /**
     * Set seeding order for participants before stage starts.
     *
     * @param Stage $stage
     * @param array $seeds Array of participant IDs in seed order [seed1_id, seed2_id, ...]
     */
    public function seedParticipants(Stage $stage, array $seeds): void
    {
        if (!$stage->isPending()) {
            throw ValidationException::withMessages([
                'stage' => ['Seeding can only be set before the stage starts.'],
            ]);
        }

        $tournament = $stage->tournament;

        // Validate all seeds are valid participant IDs
        $approvedParticipants = $tournament->participants()
            ->where('status', 'approved')
            ->pluck('id')
            ->toArray();

        foreach ($seeds as $index => $participantId) {
            if (!in_array($participantId, $approvedParticipants)) {
                throw ValidationException::withMessages([
                    'seeds' => ["Invalid participant ID at position " . ($index + 1) . "."],
                ]);
            }
        }

        // Store seeding in the participants table
        foreach ($seeds as $index => $participantId) {
            Participant::where('id', $participantId)->update(['seed' => $index + 1]);
        }
    }

    /**
     * Randomly shuffle participant seeds.
     */
    public function shuffleParticipants(Stage $stage): void
    {
        if (!$stage->isPending()) {
            throw ValidationException::withMessages([
                'stage' => ['Shuffle hanya bisa dilakukan sebelum stage dimulai.'],
            ]);
        }

        $participantIds = $stage->tournament->participants()
            ->where('status', 'approved')
            ->pluck('id')
            ->toArray();

        if (empty($participantIds)) {
            return;
        }

        shuffle($participantIds);

        foreach ($participantIds as $index => $id) {
            Participant::where('id', $id)->update(['seed' => $index + 1]);
        }
    }

    /**
     * Start a stage and generate bracket/schedule.
     */
    public function startStage(Stage $stage): array
    {
        $tournament = $stage->tournament;

        // Validate stage is pending
        if (!$stage->isPending()) {
            throw ValidationException::withMessages([
                'stage' => ['Stage has already been started.'],
            ]);
        }

        // Validate tournament status
        if (!in_array($tournament->status, ['registration', 'ongoing'])) {
            throw ValidationException::withMessages([
                'tournament' => ['Tournament must be in registration or ongoing status.'],
            ]);
        }

        // Validate previous stage is completed (if not the first stage)
        $previousStage = $tournament->stages()
            ->where('order', '<', $stage->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousStage && !$previousStage->isCompleted()) {
            throw ValidationException::withMessages([
                'stage' => ['Previous stage must be completed before starting this one.'],
            ]);
        }

        // Get approved participants
        $participants = $tournament->participants()
            ->where('status', 'approved')
            ->orderBy('seed')
            ->orderBy('created_at')
            ->get();

        if ($participants->count() < 2) {
            throw ValidationException::withMessages([
                'participants' => ['At least 2 approved participants are required to start a stage.'],
            ]);
        }

        // Format-specific validations
        $this->validateFormatConfig($stage, $participants->count());

        // Generate bracket/schedule within a transaction
        return DB::transaction(function () use ($stage, $tournament, $participants) {
            $matchesGenerated = $this->generateBracket($stage, $participants);

            // Update stage status
            $stage->update(['status' => 'ongoing']);

            // Update tournament status to ongoing if not already
            if ($tournament->status !== 'ongoing') {
                $tournament->update(['status' => 'ongoing']);
            }

            return [
                'stage_id' => $stage->id,
                'status' => 'ongoing',
                'matches_generated' => $matchesGenerated,
            ];
        });
    }

    /**
     * Advance participants to the next stage.
     *
     * @param Stage $stage The current (completed) stage
     * @param array $advancingParticipantIds IDs of participants to advance
     */
    public function advanceParticipants(Stage $stage, array $advancingParticipantIds): array
    {
        // Validate stage is completed
        if (!$stage->isCompleted()) {
            $hasIncompletMatches = $stage->matches()
                ->whereIn('status', ['upcoming', 'ongoing'])
                ->exists();

            if ($hasIncompletMatches) {
                throw ValidationException::withMessages([
                    'stage' => ['All matches must be completed before advancing participants.'],
                ]);
            }
        }

        $tournament = $stage->tournament;

        // Find next stage
        $nextStage = $tournament->stages()
            ->where('order', '>', $stage->order)
            ->orderBy('order')
            ->first();

        if (!$nextStage) {
            throw ValidationException::withMessages([
                'stage' => ['No next stage configured. This may be the final stage.'],
            ]);
        }

        // Validate advancing participants are valid
        $approvedIds = $tournament->participants()
            ->where('status', 'approved')
            ->pluck('id')
            ->toArray();

        foreach ($advancingParticipantIds as $pid) {
            if (!in_array($pid, $approvedIds)) {
                throw ValidationException::withMessages([
                    'advancing_participants' => ["Invalid participant ID: {$pid}"],
                ]);
            }
        }

        return DB::transaction(function () use ($stage, $nextStage, $advancingParticipantIds) {
            // Mark current stage as completed
            $stage->update(['status' => 'completed']);

            return [
                'current_stage_status' => 'completed',
                'next_stage_id' => $nextStage->id,
                'advancing_count' => count($advancingParticipantIds),
            ];
        });
    }

    /**
     * Reset a stage: delete matches and set status back to pending.
     * Only allowed if no matches have scores yet.
     */
    public function resetStage(Stage $stage): void
    {
        if ($stage->isPending()) {
            throw ValidationException::withMessages([
                'stage' => ['Stage sudah dalam status pending.'],
            ]);
        }

        // Check if any match is completed or has scores
        $hasResults = $stage->matches()
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhereNotNull('winner_id')
                  ->orWhereNotNull('scores');
            })
            ->exists();

        if ($hasResults) {
            throw ValidationException::withMessages([
                'stage' => ['Stage tidak bisa direset karena sudah ada pertandingan yang selesai atau memiliki skor.'],
            ]);
        }

        DB::transaction(function () use ($stage) {
            // Delete all matches
            $stage->matches()->delete();

            // Delete groups if any
            $stage->groups()->delete();

            // Reset stage status
            $stage->update(['status' => 'pending']);
        });
    }

    /**
     * Get format recommendation based on participant count.
     */
    public function getFormatRecommendation(int $participantCount): array
    {
        $recommendations = [];

        if ($participantCount < 4) {
            $recommendations[] = [
                'type' => 'recommendation',
                'message' => 'Rekomendasi: Round Robin. Jumlah peserta terlalu sedikit untuk bracket.',
            ];
        }

        if ($participantCount >= 4 && $participantCount <= 16) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'Semua format tersedia. Single atau Double Elimination direkomendasikan.',
            ];
        }

        if ($participantCount > 32) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Round Robin akan menghasilkan banyak match. Pertimbangkan Swiss atau Group Stage.',
            ];
        }

        if ($participantCount > 64) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Double Elimination bracket akan sangat panjang. Pertimbangkan Group Stage terlebih dahulu.',
            ];
        }

        return $recommendations;
    }

    /**
     * Validate format-specific configuration before starting a stage.
     */
    protected function validateFormatConfig(Stage $stage, int $participantCount): void
    {
        switch ($stage->type) {
            case 'round_robin':
                $groupsCount = $stage->groups_count ?? 1;
                if ($participantCount % $groupsCount !== 0) {
                    throw ValidationException::withMessages([
                        'groups_count' => ['Jumlah peserta harus habis dibagi jumlah grup.'],
                    ]);
                }
                break;

            case 'swiss':
                // Validate max rounds
                $maxRounds = (int) ceil(log($participantCount, 2)) + 2;
                $configuredRounds = $stage->settings['rounds'] ?? $maxRounds;
                if ($configuredRounds > $maxRounds) {
                    throw ValidationException::withMessages([
                        'settings.rounds' => ["Jumlah ronde Swiss tidak boleh melebihi {$maxRounds}."],
                    ]);
                }
                break;
        }

        // Validate participants_advance
        if ($stage->participants_advance && $stage->participants_advance >= $participantCount) {
            throw ValidationException::withMessages([
                'participants_advance' => ['Jumlah peserta yang advance harus lebih kecil dari total peserta.'],
            ]);
        }
    }

    /**
     * Generate bracket/schedule based on the stage format.
     */
    protected function generateBracket(Stage $stage, Collection $participants): int
    {
        return match ($stage->type) {
            'single_elim' => (new SingleEliminationGenerator())->generate($stage, $participants),
            'double_elim' => (new DoubleEliminationGenerator())->generate($stage, $participants),
            'round_robin' => (new RoundRobinGenerator())->generate($stage, $participants),
            'swiss' => (new SwissGenerator())->generate($stage, $participants),
            default => throw ValidationException::withMessages([
                'type' => ['Unsupported stage format: ' . $stage->type],
            ]),
        };
    }
}
