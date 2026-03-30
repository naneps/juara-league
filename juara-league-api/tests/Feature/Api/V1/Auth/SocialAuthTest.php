<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function test_it_can_authenticate_with_google_successfully(): void
    {
        $abstractUser = $this->createMock(\Laravel\Socialite\Two\User::class);
        $abstractUser->method('getId')->willReturn('google-id-123');
        $abstractUser->method('getEmail')->willReturn('google@example.com');
        $abstractUser->method('getName')->willReturn('Google User');
        $abstractUser->method('getAvatar')->willReturn('https://example.com/avatar.jpg');

        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('stateless')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($abstractUser);

        $response = $this->getJson('/api/v1/auth/google/callback');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'avatar',
                    'google_id',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'google_id' => 'google-id-123',
        ]);
    }

    /**
     * @test
     */
    public function test_it_updates_existing_user_with_google_id_on_login(): void
    {
        User::factory()->create(['email' => 'google@example.com']);

        $abstractUser = $this->createMock(\Laravel\Socialite\Two\User::class);
        $abstractUser->method('getId')->willReturn('google-id-123');
        $abstractUser->method('getEmail')->willReturn('google@example.com');
        $abstractUser->method('getName')->willReturn('Google User');
        $abstractUser->method('getAvatar')->willReturn('https://example.com/avatar.jpg');

        Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
        Socialite::shouldReceive('stateless')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturn($abstractUser);

        $this->getJson('/api/v1/auth/google/callback');

        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'google_id' => 'google-id-123',
        ]);
        
        $this->assertEquals(1, User::where('email', 'google@example.com')->count());
    }
}
