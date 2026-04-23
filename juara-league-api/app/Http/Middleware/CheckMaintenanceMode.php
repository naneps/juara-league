<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Maintenance Mode
        if (Setting::get('maintenance_mode', false)) {
            // Allow admins to bypass maintenance mode
            $user = $request->user();
            if ($user && ($user->hasRole('admin') || $user->hasRole('super_admin'))) {
                return $next($request);
            }

            // Allow login/logout routes so admins can log in to turn it off
            if ($request->is('*/login') || $request->is('*/logout') || $request->is('*/settings/public')) {
                return $next($request);
            }

            // Allow fetching settings for the login page if needed (to show maintenance message)
            // But usually we return a specific error code
            return response()->json([
                'status' => 'error',
                'message' => 'Platform is currently under maintenance. Please try again later.',
                'code' => 'MAINTENANCE_MODE'
            ], 503);
        }

        // 2. Check Registration Enabled
        if ($request->is('*/register') && !Setting::get('registration_enabled', true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User registration is currently disabled.',
                'code' => 'REGISTRATION_DISABLED'
            ], 403);
        }

        return $next($request);
    }
}
