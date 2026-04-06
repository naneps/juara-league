<?php

namespace Tests\Feature\Api\V1\Tournament;

use App\Models\Participant;
use App\Models\Stage;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_it_can_create_a_stage_for_a_tournament(): void
    {
        $user = User::factory()->create();
        $tournament = Tournament::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        $response = $this->postJson("/api/v1/tournaments/{$tournament->slug}/stages", [
            'name' => 'Group Phase',
            'type' => 'round_robin',
            'settings' => ['bo' => 1]
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('stages', [
            'tournament_id' => $tournament->id,
            'name' => 'Group Phase'
        ]);
    }

    /**
     * @test
     */
    public function test_it_cannot_publish_tournament_without_stages(): void
    {
        $user = User::factory()->create();
        $tournament = Tournament::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
        $this->actingAs($user);

        $response = $this->postJson("/api/v1/tournaments/{$tournament->slug}/publish");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Tournament must have at least one stage before being published.');
    }

    /**
     * @test
     */
    public function test_it_can_publish_tournament_with_at_least_one_stage(): void
    {
        $user = User::factory()->create();
        $tournament = Tournament::factory()->create(['user_id' => $user->id, 'status' => 'draft']);
        Stage::factory()->create(['tournament_id' => $tournament->id]);
        $this->actingAs($user);

        $response = $this->postJson("/api/v1/tournaments/{$tournament->slug}/publish");

        $response->assertStatus(200);
        $this->assertEquals('registration', $tournament->fresh()->status);
    }

    /**
     * @test
     */
    public function test_user_can_register_for_a_tournament_in_registration_phase(): void
    {
        $tournament = Tournament::factory()->create(['status' => 'registration']);
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson("/api/v1/tournaments/{$tournament->slug}/participants", [
            'user_id' => $user->id,
            'notes' => 'I am ready!'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('participants', [
            'tournament_id' => $tournament->id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
    }

    /**
     * @test
     */
    public function test_owner_can_approve_participant(): void
    {
        $user = User::factory()->create();
        $tournament = Tournament::factory()->create(['user_id' => $user->id]);
        $participant = Participant::factory()->create(['tournament_id' => $tournament->id, 'status' => 'pending']);
        $this->actingAs($user);

        $response = $this->patchJson("/api/v1/participants/{$participant->id}/status", [
            'status' => 'approved'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('approved', $participant->fresh()->status);
    }
}
