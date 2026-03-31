<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @param SocialiteUser $socialUser
     * @return array
     */
    public function handleGoogleCallback(SocialiteUser $socialUser): array
    {
        $user = $this->userRepository->findByGoogleId($socialUser->getId());

        if (! $user) {
            $userByEmail = $this->userRepository->findByEmail($socialUser->getEmail());

            if ($userByEmail) {
                $user = $this->userRepository->update($userByEmail, [
                    'google_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
            } else {
                $user = $this->userRepository->create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'google_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
