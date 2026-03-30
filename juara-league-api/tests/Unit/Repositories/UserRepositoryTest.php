<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
    }

    /**
     * @test
     */
    public function test_it_can_find_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $foundUser = $this->userRepository->findByEmail('test@example.com');

        $this->assertEquals($user->id, $foundUser->id);
    }

    /**
     * @test
     */
    public function test_it_can_find_user_by_google_id(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123']);

        $foundUser = $this->userRepository->findByGoogleId('google-123');

        $this->assertEquals($user->id, $foundUser->id);
    }

    /**
     * @test
     */
    public function test_it_can_create_a_user(): void
    {
        $data = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
        ];

        $user = $this->userRepository->create($data);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @test
     */
    public function test_it_can_update_a_user(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $updatedUser = $this->userRepository->update($user, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertDatabaseHas('users', ['name' => 'New Name']);
    }
}
