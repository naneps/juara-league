<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected $userRepository;
    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    /**
     * @test
     */
    public function test_it_can_register_a_user(): void
    {
        Event::fake([Registered::class]);

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('createToken')->andReturn((object)['plainTextToken' => 'token']);

        $this->userRepository->shouldReceive('create')->once()->andReturn($user);

        $result = $this->authService->register($data);

        $this->assertEquals($user, $result['user']);
        $this->assertEquals('token', $result['token']);

        Event::assertDispatched(Registered::class);
    }

    /**
     * @test
     */
    public function test_it_can_login_a_user_with_valid_credentials(): void
    {
        $email = 'test@example.com';
        $password = 'password';

        $user = Mockery::mock(User::class)->makePartial();
        $user->password = Hash::make($password);
        $user->shouldReceive('createToken')->andReturn((object)['plainTextToken' => 'token']);

        $this->userRepository->shouldReceive('findByEmail')->with($email)->once()->andReturn($user);

        $result = $this->authService->login($email, $password);

        $this->assertEquals($user, $result['user']);
        $this->assertEquals('token', $result['token']);
    }

    /**
     * @test
     */
    public function test_it_can_handle_google_callback_for_new_user(): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-id-123');
        $socialUser->shouldReceive('getEmail')->andReturn('google@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Google User');
        $socialUser->shouldReceive('getAvatar')->andReturn('avatar.jpg');

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('createToken')->andReturn((object)['plainTextToken' => 'token']);

        $this->userRepository->shouldReceive('findByGoogleId')->with('google-id-123')->once()->andReturn(null);
        $this->userRepository->shouldReceive('findByEmail')->with('google@example.com')->once()->andReturn(null);
        $this->userRepository->shouldReceive('create')->once()->andReturn($user);

        $result = $this->authService->handleGoogleCallback($socialUser);

        $this->assertEquals($user, $result['user']);
        $this->assertEquals('token', $result['token']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
