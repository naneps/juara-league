<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Mail\TeamInvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TeamService
{
    /**
     * Create a new team and add the creator as captain and member.
     */
    public function createTeam(array $data, User $user): Team
    {
        return DB::transaction(function () use ($data, $user) {
            $data['captain_id'] = $user->id;
            $team = Team::create($data);

            $team->members()->attach($user->id, ['joined_at' => now()]);

            return $team;
        });
    }

    /**
     * Create a team invitation.
     */
    public function createInvitation(Team $team, string $email): TeamInvitation
    {
        // Check if user already a member
        $user = User::where('email', $email)->first();
        if ($user && $team->members()->where('user_id', $user->id)->exists()) {
            throw new \Exception('User is already a member of this team.', 422);
        }

        // Check if there's an existing pending invitation
        $existing = $team->invitations()->where('email', $email)->where('status', 'pending')->where('expires_at', '>', now())->first();
        if ($existing) {
            return $existing;
        }

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'email' => $email,
            'token' => Str::random(32),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->load(['team.captain']);

        Mail::to($email)->send(new TeamInvitationMail($invitation));

        return $invitation;
    }

    /**
     * Accept a team invitation.
     */
    public function acceptInvitation(string $token, User $user): Team
    {
        return DB::transaction(function () use ($token, $user) {
            $invitation = TeamInvitation::where('token', $token)
                ->where('email', $user->email)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->firstOrFail();

            $team = $invitation->team;

            if ($team->members()->where('user_id', $user->id)->exists()) {
                $invitation->update(['status' => 'accepted']);
                return $team;
            }

            $team->members()->attach($user->id, ['joined_at' => now()]);
            $invitation->update(['status' => 'accepted']);

            return $team;
        });
    }

    /**
     * Decline a team invitation.
     */
    public function declineInvitation(string $token, User $user): void
    {
        $invitation = TeamInvitation::where('token', $token)
            ->where('email', $user->email)
            ->where('status', 'pending')
            ->firstOrFail();

        $invitation->update(['status' => 'declined']);
    }

    /**
     * Transfer captaincy to another member.
     */
    public function transferCaptaincy(Team $team, User $newCaptain): void
    {
        if (!$team->members()->where('user_id', $newCaptain->id)->exists()) {
            throw new \Exception('Target user must be a member of the team.', 422);
        }

        $team->update(['captain_id' => $newCaptain->id]);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, User $member): void
    {
        if ($team->captain_id === $member->id) {
            throw new \Exception('Captain cannot be removed from the team. Transfer ownership first.', 422);
        }

        // Check if active in tournament (Place holder for Modul 4)
        // if ($this->isMemberActiveInTournament($team, $member)) { ... }

        $team->members()->detach($member->id);
    }
}
