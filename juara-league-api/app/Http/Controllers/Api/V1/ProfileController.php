<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * Update user profile information.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfile($request->user(), $request->validated());

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->profileService->updatePassword($request->user(), $request->new_password);

        return response()->json([
            'message' => 'Password berhasil diubah. Sesi Anda di perangkat lain telah diakhiri.',
        ]);
    }
}
