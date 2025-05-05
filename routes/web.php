<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GithubAuthController;


Route::get('/auth/github/redirect', [GithubAuthController::class, 'redirectToGithub'])->name('github.redirect');
Route::get('/auth/github/callback', [GithubAuthController::class, 'handleGithubCallback'])->name('github.callback');
