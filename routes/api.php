<?php

use App\Http\Controllers\Auth\GithubAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProjectController;

// Public routes
Route::prefix('auth/github')->group(function () {
    Route::get('authorize', [GithubAuthController::class, 'redirectToGithub']);
    Route::get('callback', [GithubAuthController::class, 'handleGithubCallback']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'authenticated' => true
        ]);
    });

    Route::get('/auth/github/user', [GithubAuthController::class, 'getAuthenticatedUser']);
    Route::post('/projects', [ProjectController::class, 'createRepo']);
});
