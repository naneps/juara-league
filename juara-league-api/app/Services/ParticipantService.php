<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Events\ParticipantManuallyRegistered;
use Illuminate\Support\Str;
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

        // Validate Participant Type
        if ($tournament->participant_type === 'individual') {
            if (!isset($data['user_id'])) {
                throw ValidationException::withMessages([
                    'user_id' => ['User ID is required for individual tournaments.']
                ]);
            }
            // Do not allow team_id for individual
            unset($data['team_id']);
        } else if ($tournament->participant_type === 'team') {
            if (!isset($data['team_id'])) {
                throw ValidationException::withMessages([
                    'team_id' => ['Team ID is required for team-based tournaments.']
                ]);
            }
            // user_id is kept - it records who submitted the registration
        }

        // Check capacity
        if ($tournament->max_participants > 0 && 
            $tournament->participants()->where('status', '!=', 'rejected')->count() >= $tournament->max_participants) {
            throw ValidationException::withMessages([
                'tournament' => ['Tournament participant capacity reached.']
            ]);
        }

        // Check if already registered (Active participation)
        // For individual: check by user_id
        // For team: check by team_id
        if ($tournament->participant_type === 'individual' && isset($data['user_id'])) {
            $alreadyRegistered = $tournament->participants()
                ->where('status', '!=', 'rejected')
                ->where('user_id', $data['user_id'])
                ->exists();
        } else if ($tournament->participant_type === 'team' && isset($data['team_id'])) {
            $alreadyRegistered = $tournament->participants()
                ->where('status', '!=', 'rejected')
                ->where('team_id', $data['team_id'])
                ->exists();
        } else {
            $alreadyRegistered = false;
        }

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'registration' => ['Already registered for this tournament.']
            ]);
        }

        $data['tournament_id'] = $tournament->id;
        $data['status'] = 'pending';

        return Participant::create($data);
    }

    public function registerManual(Tournament $tournament, array $data): Participant
    {
        // Require either email or phone
        if (empty($data['email']) && empty($data['phone'])) {
            throw ValidationException::withMessages([
                'email' => ['Either Email or Phone Number is required.']
            ]);
        }

        // Find or create User
        $user = null;
        if (!empty($data['email'])) {
            $user = User::where('email', $data['email'])->first();
        }
        if (!$user && !empty($data['phone'])) {
            $user = User::where('phone', $data['phone'])->first();
        }

        $isNewUser = false;
        $rawPassword = null;
        if (!$user) {
            $isNewUser = true;
            $rawPassword = Str::random(12);
            $email = !empty($data['email']) ? $data['email'] : preg_replace('/[^0-9]/', '', $data['phone']) . '@guest.juaraleague.com';
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($rawPassword), // Random password for guest
                // Optionally set an is_guest flag if you add it to the DB later
            ]);
        }

        $participantData = [
            'tournament_id' => $tournament->id,
            'user_id' => $user->id,
            'status' => 'approved', // Directly approved by organizer
            'notes' => 'Registered manually by Organizer.',
        ];

        // Handle Team validation
        if ($tournament->participant_type === 'team') {
            if (empty($data['team_name'])) {
                throw ValidationException::withMessages([
                    'team_name' => ['Team Name is required for team-based tournaments.']
                ]);
            }

            // Check if user already manages a team with this name
            $team = Team::where('captain_id', $user->id)
                        ->where('name', $data['team_name'])
                        ->first();

            if (!$team) {
                // Create Team automatically
                $team = Team::create([
                    'name' => $data['team_name'],
                    'captain_id' => $user->id,
                    'status' => 'active'
                ]);
                
                // Add user to team_members
                $team->members()->attach($user->id, [
                    'id' => (string) Str::ulid(),
                    'joined_at' => now()
                ]);
            }

            $participantData['team_id'] = $team->id;
            
            // Check if this team is already registered
            $alreadyRegistered = $tournament->participants()
                ->where('status', '!=', 'rejected')
                ->where('team_id', $participantData['team_id'])
                ->exists();
        } else {
            // Individual check
            $alreadyRegistered = $tournament->participants()
                ->where('status', '!=', 'rejected')
                ->where('user_id', $participantData['user_id'])
                ->exists();
        }

        if ($alreadyRegistered) {
            throw ValidationException::withMessages([
                'registration' => ['Participant is already registered for this tournament.']
            ]);
        }

        // Capacity check
        if ($tournament->max_participants > 0 && 
            $tournament->participants()->where('status', '!=', 'rejected')->count() >= $tournament->max_participants) {
            throw ValidationException::withMessages([
                'tournament' => ['Tournament participant capacity reached.']
            ]);
        }

        $participant = Participant::create($participantData);

        // Dispatch event immediately (Event Listener will handle if it should send an email or not)
        if ($isNewUser) {
            ParticipantManuallyRegistered::dispatch(
                $user,
                $tournament,
                $rawPassword,
                $data['team_name'] ?? null
            );
        }

        return $participant;
    }

    public function updateStatus(Participant $participant, string $status): Participant
    {
        $participant->update(['status' => $status]);
        return $participant;
    }

    public function getUserParticipations(string $userId): Collection
    {
        return Participant::where('user_id', $userId)
            ->with(['tournament.sport', 'tournament.user', 'team'])
            ->latest()
            ->get();
    }

    public function deleteParticipant(Participant $participant): bool
    {
        return $participant->delete();
    }
}
