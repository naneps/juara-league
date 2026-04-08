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
        protected UserRepositoryInterface $userRepository,
        protected FileService $fileService
    ) {}

    /**
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $updateData = [
            'name' => $data['name'] ?? $user->name,
            'username' => $data['username'] ?? $user->username,
            'email' => $data['email'] ?? $user->email,
            'bio' => $data['bio'] ?? $user->bio,
            'phone' => $data['phone'] ?? $user->phone,
        ];

        // Handle avatar upload
        if (isset($data['avatar_file']) && $data['avatar_file'] instanceof UploadedFile) {
            // Optional: Delete old avatar if it exists locally
            if ($user->avatar && str_contains($user->avatar, config('app.url'))) {
                $oldPath = $this->fileService->getPathFromUrl($user->avatar);
                $this->fileService->delete($oldPath);
            }

            $uploadResult = $this->fileService->upload($data['avatar_file'], 'avatars');
            $updateData['avatar'] = $uploadResult['url'];
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
