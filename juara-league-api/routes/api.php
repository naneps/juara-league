<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TournamentController;
use App\Http\Controllers\Api\V1\TournamentStaffController;
use App\Http\Controllers\Api\V1\StageController;
use App\Http\Controllers\Api\V1\ParticipantController;
use App\Http\Controllers\Api\V1\VerificationController;
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

        // Tournament Management
        Route::get('/my-tournaments', [TournamentController::class, 'mine']);
        Route::post('/tournaments', [TournamentController::class, 'store']);
        Route::put('/tournaments/{tournament}', [TournamentController::class, 'update']);
        Route::delete('/tournaments/{tournament}', [TournamentController::class, 'destroy']);
        Route::post('/tournaments/{tournament:slug}/publish', [TournamentController::class, 'publish']);

        // Tournament Staff Management
        Route::get('/tournaments/{tournament:slug}/staff', [TournamentStaffController::class, 'index']);
        Route::post('/tournaments/{tournament:slug}/staff', [TournamentStaffController::class, 'store']);
        Route::delete('/tournaments/{tournament:slug}/staff/{user}', [TournamentStaffController::class, 'destroy']);

        // Stage Management
        Route::post('/tournaments/{tournament:slug}/stages', [StageController::class, 'store']);
        Route::put('/stages/{stage}', [StageController::class, 'update']);
        Route::delete('/stages/{stage}', [StageController::class, 'destroy']);

        // Participant Management
        Route::post('/tournaments/{tournament:slug}/participants', [ParticipantController::class, 'store']);
        Route::patch('/participants/{participant}/status', [ParticipantController::class, 'updateStatus']);
        Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy']);

        // Admin - Sport Management
        Route::prefix('admin')->group(function () {
            Route::post('/sports', [\App\Http\Controllers\Api\V1\Admin\SportController::class, 'store']);
            Route::put('/sports/{id}', [\App\Http\Controllers\Api\V1\Admin\SportController::class, 'update']);
            Route::delete('/sports/{id}', [\App\Http\Controllers\Api\V1\Admin\SportController::class, 'destroy']);
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
