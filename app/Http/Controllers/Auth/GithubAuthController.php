<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GithubAuthController extends Controller
{
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGithubCallback(Request $request)
    {
        try {
            Log::info('GitHub callback received', [
                'code' => $request->has('code') ? 'present' : 'missing',
                'state' => $request->input('state'),
                'all' => $request->all(),
            ]);

            $githubUser = Socialite::driver('github')->stateless()->user(); // Try stateless mode
            Log::info('GitHub User Data', (array)$githubUser);

            $user = User::updateOrCreate(
                ['github_id' => $githubUser->id],
                [
                    'name' => $githubUser->name,
                    'email' => $githubUser->email ?? ($githubUser->nickname . '@github.com'),
                    'github_token' => $githubUser->token,
                    'github_refresh_token' => $githubUser->refreshToken,
                ]
            );

            Auth::login($user);
            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = urlencode(json_encode([
                'id' => $githubUser->id,
                'name' => $githubUser->name,
                'email' => $githubUser->email ?? ($githubUser->nickname . '@github.com'),
            ]));

            return redirect("http://localhost:4200/login-callback?token={$token}&user={$userData}");
        } catch (\Exception $e) {
            Log::error('GitHub Callback Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return redirect('http://localhost:4200/login-callback?error=' . urlencode($e->getMessage()));
        }
    }
}
