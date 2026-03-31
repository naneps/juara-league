<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $updateData = ['name' => $data['name']];

        // Handle avatar upload to local storage (mocking R2 for now)
        if (isset($data['avatar_file']) && $data['avatar_file'] instanceof UploadedFile) {
            $path = $data['avatar_file']->store('avatars', 'public');
            $updateData['avatar'] = url(Storage::url($path));
        } elseif (isset($data['avatar_url'])) {
            $updateData['avatar'] = $data['avatar_url'];
        }

        return $this->userRepository->update($user, $updateData);
    }

    /**
     * @param User $user
     * @param string $newPassword
     * @return void
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        // Update the user's password
        $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);

        // Revoke all tokens so the user is logged out of other devices
        $user->tokens()->delete();
    }
}
