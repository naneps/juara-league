<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->latest()->paginate($request->query('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        // Prevent self-demotion if the current user is an admin
        if ($user->id === auth()->id() && $request->role !== 'admin' && $user->hasRole('admin')) {
             return response()->json(['message' => 'Anda tidak bisa menurunkan role diri sendiri.'], 403);
        }

        $user->syncRoles([$request->role]);

        return response()->json([
            'message' => "Role user berhasil diubah menjadi {$request->role}.",
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Toggle user suspension status.
     */
    public function toggleSuspension(User $user): JsonResponse
    {
        // Prevent self-suspension
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak bisa memblokir diri sendiri.'], 403);
        }

        $user->update([
            'is_suspended' => !$user->is_suspended
        ]);

        $status = $user->is_suspended ? 'diblokir' : 'diaktifkan kembali';

        return response()->json([
            'message' => "User berhasil {$status}.",
            'data' => new UserResource($user)
        ]);
    }

    /**
     * Delete user account.
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun sendiri dari sini.'], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.'
        ]);
    }
}
