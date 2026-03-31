<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TournamentController;
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

    Route::get('/tournaments', [\App\Http\Controllers\Api\V1\TournamentController::class, 'index']);
    Route::get('/tournaments/{slug}', [\App\Http\Controllers\Api\V1\TournamentController::class, 'show']);

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
        Route::get('/my-tournaments', [\App\Http\Controllers\Api\V1\TournamentController::class, 'mine']);
        Route::post('/tournaments', [\App\Http\Controllers\Api\V1\TournamentController::class, 'store']);
        Route::put('/tournaments/{tournament}', [\App\Http\Controllers\Api\V1\TournamentController::class, 'update']);
        Route::delete('/tournaments/{tournament}', [\App\Http\Controllers\Api\V1\TournamentController::class, 'destroy']);
    });
});
