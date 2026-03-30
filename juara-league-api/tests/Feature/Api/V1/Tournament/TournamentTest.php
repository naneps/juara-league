<?php

namespace Tests\Feature\Api\V1\Tournament;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_it_can_list_all_tournaments(): void
    {
        Tournament::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/tournaments');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'category', 'status']
                ],
                'meta',
                'links'
            ]);
    }

    /**
     * @test
     */
    public function test_it_can_view_a_single_tournament_by_slug(): void
    {
        $tournament = Tournament::factory()->create(['title' => 'Epic Cup']);

        $response = $this->getJson("/api/v1/tournaments/{$tournament->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Epic Cup');
    }

    /**
     * @test
     */
    public function test_authenticated_user_can_create_a_tournament(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tournamentData = [
            'title' => 'New Season 2026',
            'category' => 'Football',
            'mode' => 'online',
            'bracket_type' => 'single',
            'max_participants' => 16,
            'prize_pool' => 1000000,
        ];

        $response = $this->postJson('/api/v1/tournaments', $tournamentData);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New Season 2026')
            ->assertJsonPath('data.slug', 'new-season-2026');

        $this->assertDatabaseHas('tournaments', [
            'title' => 'New Season 2026',
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     */
    public function test_it_generates_unique_slugs_for_duplicate_titles(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Tournament::factory()->create(['title' => 'Duplicate Title', 'slug' => 'duplicate-title']);

        $response = $this->postJson('/api/v1/tournaments', [
            'title' => 'Duplicate Title',
            'category' => 'Football',
            'mode' => 'online',
            'bracket_type' => 'single',
            'max_participants' => 16,
        ]);

        $response->assertStatus(201);
        $this->assertNotEquals('duplicate-title', $response->json('data.slug'));
        $this->assertStringContainsString('duplicate-title-', $response->json('data.slug'));
    }

    /**
     * @test
     */
    public function test_owner_can_update_their_tournament(): void
    {
        $user = User::factory()->create();
        $tournament = Tournament::factory()->create(['user_id' => $user->id, 'title' => 'Old Title']);
        
        $this->actingAs($user);

        $response = $this->putJson("/api/v1/tournaments/{$tournament->id}", [
            'title' => 'Updated Awesome Title'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Awesome Title');
            
        $this->assertDatabaseHas('tournaments', [
            'id' => $tournament->id,
            'title' => 'Updated Awesome Title'
        ]);
    }

    /**
     * @test
     */
    public function test_non_owner_cannot_update_tournament(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $tournament = Tournament::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($stranger);

        $response = $this->putJson("/api/v1/tournaments/{$tournament->id}", [
            'title' => 'Attempted Hack'
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function test_it_validates_required_fields_on_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/tournaments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'category', 'mode', 'bracket_type', 'max_participants']);
    }
}
