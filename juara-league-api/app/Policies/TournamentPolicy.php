<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TournamentPolicy
{
    /**
     * Determine whether the user can update the tournament.
     */
    public function update(User $user, Tournament $tournament): bool
    {
        // Owner and Co-Organizer can update
        return $tournament->user_id === $user->id
            || $tournament->staff()->where('user_id', $user->id)
                ->whereIn('role', ['owner', 'co_organizer'])
                ->exists();
    }

    /**
     * Determine whether the user can delete the tournament.
     */
    public function delete(User $user, Tournament $tournament): bool
    {
        // Only Owner can delete
        // BR12: Ongoing or completed tournaments cannot be deleted
        if (in_array($tournament->status, ['ongoing', 'completed'])) {
            return false;
        }

        return $tournament->user_id === $user->id;
    }

    /**
     * Determine whether the user can publish the tournament.
     */
    public function publish(User $user, Tournament $tournament): bool
    {
        // Only Owner can publish
        // Rule: Approval status must not be rejected or pending_review
        return $tournament->user_id === $user->id
            && $tournament->approval_status === 'auto_approved' // Or 'approved' after manual review
            && $tournament->status === 'draft';
    }

    /**
     * Determine whether the user can manage staff.
     */
    public function manageStaff(User $user, Tournament $tournament): bool
    {
        // Only Owner can manage staff
        return $tournament->user_id === $user->id;
    }

    /**
     * Determine whether the user can manage participants.
     */
    public function manageParticipants(User $user, Tournament $tournament): bool
    {
        // Staff members (Owner, Co-Organizer, Referee) can manage participants
        return $tournament->user_id === $user->id
            || $tournament->staff()->where('user_id', $user->id)
                ->whereIn('role', ['owner', 'co_organizer', 'referee'])
                ->exists();
    }
}
