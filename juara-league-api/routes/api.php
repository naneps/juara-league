<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TournamentController;
use App\Http\Controllers\Api\V1\TournamentStaffController;
use App\Http\Controllers\Api\V1\StageController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\ParticipantController;
use App\Http\Controllers\Api\V1\VerificationController;
use App\Http\Controllers\Api\V1\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Auth Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::prefix('auth/google')->group(function () {
        Route::get('/redirect', [AuthController::class, 'googleRedirect']);
        Route::get('/callback', [AuthController::class, 'googleCallback']);
    });

    Route::get('/auth/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
         ->name('verification.verify');

    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/{slug}', [TournamentController::class, 'show']);
    Route::get('/tournaments/{tournament:slug}/stages', [StageController::class, 'index']);
    Route::get('/tournaments/{tournament:slug}/stages/{stage}', [StageController::class, 'show']);
    Route::get('/tournaments/{tournament:slug}/stages/{stage}/matches', [MatchController::class, 'index']);
    Route::get('/tournaments/{tournament:slug}/stages/{stage}/matches/{match}', [MatchController::class, 'show']);
    Route::get('/tournaments/{tournament:slug}/participants', [ParticipantController::class, 'index']);

    Route::get('/sports', [\App\Http\Controllers\Api\V1\SportController::class, 'index']);
    Route::get('/sports/{id}', [\App\Http\Controllers\Api\V1\SportController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    | Routes in this group require a valid session/token via Sanctum.
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/auth/email/resend', [VerificationController::class, 'resend'])
             ->middleware('throttle:6,1')->name('verification.send');

        Route::put('/users/me', [ProfileController::class, 'updateProfile']);
        Route::put('/users/password', [ProfileController::class, 'updatePassword']);

        // File Upload
        Route::post('/upload', [FileController::class, 'upload']);

        // Tournament Management
        Route::get('/my-tournaments', [TournamentController::class, 'mine']);
        Route::post('/tournaments', [TournamentController::class, 'store']);
        Route::put('/tournaments/{tournament:slug}', [TournamentController::class, 'update']);
        Route::delete('/tournaments/{tournament:slug}', [TournamentController::class, 'destroy']);
        Route::post('/tournaments/{tournament:slug}/publish', [TournamentController::class, 'publish']);

        // Tournament Staff Management
        Route::get('/tournaments/{tournament:slug}/staff', [TournamentStaffController::class, 'index']);
        Route::post('/tournaments/{tournament:slug}/staff', [TournamentStaffController::class, 'store']);
        Route::delete('/tournaments/{tournament:slug}/staff/{user}', [TournamentStaffController::class, 'destroy']);

        // Stage Management
        Route::post('/tournaments/{tournament:slug}/stages', [StageController::class, 'store']);
        Route::put('/tournaments/{tournament:slug}/stages/{stage}', [StageController::class, 'update']);
        Route::delete('/tournaments/{tournament:slug}/stages/{stage}', [StageController::class, 'destroy']);
        Route::post('/tournaments/{tournament:slug}/stages/{stage}/seed', [StageController::class, 'seed']);
        Route::post('/tournaments/{tournament:slug}/stages/{stage}/start', [StageController::class, 'start']);
        Route::post('/tournaments/{tournament:slug}/stages/{stage}/advance', [StageController::class, 'advance']);

        // Match & Game Management
        Route::patch('/tournaments/{tournament:slug}/stages/{stage}/matches/{match}', [MatchController::class, 'update']);
        Route::post('/tournaments/{tournament:slug}/stages/{stage}/matches/{match}/games', [MatchController::class, 'storeGame']);
        Route::put('/tournaments/{tournament:slug}/stages/{stage}/matches/{match}/games/{game}', [MatchController::class, 'updateGame']);

        // Participant Management
        Route::get('/my-participations', [ParticipantController::class, 'mine']);
        Route::post('/tournaments/{tournament:slug}/participants', [ParticipantController::class, 'store']);
        Route::post('/tournaments/{tournament:slug}/participants/manual', [ParticipantController::class, 'storeManual']);
        Route::patch('/participants/{participant}/status', [ParticipantController::class, 'updateStatus']);
        Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy']);

        // Admin - Management
        Route::prefix('admin')->middleware('role:admin|super_admin')->group(function () {
            // Dashboard Stats
            Route::get('/stats', [\App\Http\Controllers\Api\V1\Admin\StatsController::class, 'index']);

            // Sports
            Route::post('/sports', [\App\Http\Controllers\Api\V1\Admin\SportController::class, 'store']);
            Route::put('/sports/{id}', [\App\Http\Controllers\Api\V1\Admin\SportController::class, 'update']);
            Route::delete('/sports/{id}', [\App\Http\Controllers\Api\V1\Admin\SportController::class, 'destroy']);

            // Tournaments
            Route::get('/tournaments', [\App\Http\Controllers\Api\V1\Admin\TournamentController::class, 'index']);
            Route::post('/tournaments/{id}/approve', [\App\Http\Controllers\Api\V1\Admin\TournamentController::class, 'approve']);
            Route::post('/tournaments/{id}/reject', [\App\Http\Controllers\Api\V1\Admin\TournamentController::class, 'reject']);

            // Users
            Route::get('/users', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'index']);
            Route::patch('/users/{user}/role', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'updateRole']);
            Route::patch('/users/{user}/suspend', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'toggleSuspension']);
            Route::delete('/users/{user}', [\App\Http\Controllers\Api\V1\Admin\UserController::class, 'destroy']);
        });

        // Team Management
        Route::get('/my-teams', [\App\Http\Controllers\Api\V1\TeamController::class, 'mine']);
        Route::get('/my-invitations', [\App\Http\Controllers\Api\V1\TeamMemberController::class, 'myInvitations']);
        Route::apiResource('teams', \App\Http\Controllers\Api\V1\TeamController::class);
        
        Route::post('/teams/{team}/invite', [\App\Http\Controllers\Api\V1\TeamMemberController::class, 'invite']);
        Route::post('/teams/invitations/{token}/accept', [\App\Http\Controllers\Api\V1\TeamMemberController::class, 'accept']);
        Route::post('/teams/invitations/{token}/decline', [\App\Http\Controllers\Api\V1\TeamMemberController::class, 'decline']);
        Route::delete('/teams/{team}/members/{user}', [\App\Http\Controllers\Api\V1\TeamMemberController::class, 'remove']);
        Route::post('/teams/{team}/transfer', [\App\Http\Controllers\Api\V1\TeamMemberController::class, 'transfer']);
    });
});
