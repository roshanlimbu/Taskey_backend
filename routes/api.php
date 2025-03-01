<?php
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/auth/github', function () {
    return Socialite::driver('github')->redirect();
});

Route::get('/auth/github/callback', function () {
    $githubUser = Socialite::driver('github')->user();

    $user = User::where('github_id', $githubUser->id)->first();

    if (!$user) {
        $user = User::create([
            'name' => $githubUser->name ?? $githubUser->nickname,
            'email' => $githubUser->email,
            'github_id' => $githubUser->id,
            'github_token' => $githubUser->token,
        ]);
    }

    Auth::login($user);

    return redirect('/dashboard');
});
