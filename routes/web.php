<?php
use App\Http\Controllers\Auth\GithubAuthController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/auth/github', [GitHubAuthController::class, 'redirectToGitHub']);
Route::get('/auth/github/callback', [GitHubAuthController::class, 'handleGitHubCallback']);
