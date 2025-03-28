<?php

use App\Http\Controllers\Auth\GithubAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('auth')->get('/user', function (Request $request) {
    return response()->json(
        [
            'user' => Auth::user(),
            'authenticated' => Auth::check(),
        ]
    );
});
Route::get('/auth/github', [GithubAuthController::class, 'redirectToGithub']);
Route::get('/auth/github/callback', [GithubAuthController::class, 'handleGithubCallback']);
