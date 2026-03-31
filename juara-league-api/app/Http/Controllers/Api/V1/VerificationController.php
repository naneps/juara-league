<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class VerificationController extends Controller
{
    /**
     * Verify the user's email address.
     * When a user clicks the link in their email, it hits this endpoint.
     */
    public function verify(Request $request, $id, $hash): RedirectResponse
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $user = User::findOrFail($id);

        // Check hash
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->to($frontendUrl . '/login?error=invalid_verification_link');
        }

        // Check signature expiration & validity
        if (! $request->hasValidSignature()) {
            return redirect()->to($frontendUrl . '/login?error=verification_signature_invalid');
        }

        // Attempt verification
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->to($frontendUrl . '/login?verified=1');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email address is already verified.',
            ], 400); // Bad Request since nothing to do
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link has been sent to your email address.',
        ]);
    }
}
