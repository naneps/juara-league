<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * @param AuthService $authService
     */
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully.',
            'data' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 201);
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password
        );

        return response()->json([
            'message' => 'Login successful.',
            'data' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function googleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->user();
            $result = $this->authService->handleGoogleCallback($socialUser);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            
            return redirect()->to($frontendUrl . '/auth/callback?token=' . urlencode($result['token']));
        } catch (\Exception $e) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->to($frontendUrl . '/login?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
