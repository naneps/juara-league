<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_team()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/teams', [
            'name' => 'Team Alpha',
            'description' => 'Alpha Team Description',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('teams', ['name' => 'Team Alpha', 'captain_id' => $user->id]);
        $this->assertDatabaseHas('team_members', ['user_id' => $user->id]);
    }

    public function test_captain_can_invite_member()
    {
        \Illuminate\Support\Facades\Mail::fake();

        $captain = User::factory()->create();
        $team = Team::factory()->create(['captain_id' => $captain->id]);
        $team->members()->attach($captain->id, ['joined_at' => now()]);

        $targetUser = User::factory()->create();

        $response = $this->actingAs($captain)->postJson("/api/v1/teams/{$team->id}/invite", [
            'email' => $targetUser->email,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'email' => $targetUser->email,
            'status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\TeamInvitationMail::class, function ($mail) use ($targetUser) {
            return $mail->hasTo($targetUser->email);
        });
    }

    public function test_user_can_accept_invitation()
    {
        $captain = User::factory()->create();
        $team = Team::factory()->create(['captain_id' => $captain->id]);
        
        $targetUser = User::factory()->create();
        $invitation = TeamInvitation::create([
            'team_id' =>  $team->id,
            'email' => $targetUser->email,
            'token' => 'test-token',
            'status' => 'pending',
            'expires_at' => now()->addDays(1),
        ]);

        $response = $this->actingAs($targetUser)->postJson("/api/v1/teams/invitations/test-token/accept");

        $response->assertStatus(200);
        $this->assertDatabaseHas('team_members', ['team_id' => $team->id, 'user_id' => $targetUser->id]);
        $this->assertDatabaseHas('team_invitations', ['token' => 'test-token', 'status' => 'accepted']);
    }

    public function test_captain_can_transfer_ownership()
    {
        $captain = User::factory()->create();
        $team = Team::factory()->create(['captain_id' => $captain->id]);
        $team->members()->attach($captain->id, ['joined_at' => now()]);

        $member = User::factory()->create();
        $team->members()->attach($member->id, ['joined_at' => now()]);

        $response = $this->actingAs($captain)->postJson("/api/v1/teams/{$team->id}/transfer", [
            'user_id' => $member->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($member->id, $team->fresh()->captain_id);
    }
}
