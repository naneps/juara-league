<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ParticipantService
{
    public function getTournamentParticipants(Tournament $tournament): Collection
    {
        return $tournament->participants()->with(['user', 'team'])->get();
    }

    public function register(Tournament $tournament, array $data): Participant
    {
        // Check if tournament is open for registration
        if ($tournament->status !== 'registration') {
            throw ValidationException::withMessages([
                'tournament' => ['Tournament is not open for registration.']
            ]);
        }

        // Check capacity
        if ($tournament->max_participants > 0 && 
            $tournament->participants()->where('status', '!=', 'rejected')->count() >= $tournament->max_participants) {
            throw ValidationException::withMessages([
                'tournament' => ['Tournament participant capacity reached.']
            ]);
        }

        // Check if already registered
        $query = $tournament->participants();
        if (isset($data['user_id'])) {
            $query->where('user_id', $data['user_id']);
        }
        if (isset($data['team_id'])) {
            $query->where('team_id', $data['team_id']);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'registration' => ['Already registered for this tournament.']
            ]);
        }

        $data['tournament_id'] = $tournament->id;
        $data['status'] = 'pending';

        return Participant::create($data);
    }

    public function updateStatus(Participant $participant, string $status): Participant
    {
        $participant->update(['status' => $status]);
        return $participant;
    }

    public function deleteParticipant(Participant $participant): bool
    {
        return $participant->delete();
    }
}
