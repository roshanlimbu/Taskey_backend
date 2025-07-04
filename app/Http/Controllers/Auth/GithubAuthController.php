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
        return Socialite::driver('github')->stateless()->redirect();
    }

    public function handleGithubCallback(Request $request)
    {
        try {
            Log::info('GitHub callback received', [
                'code' => $request->has('code') ? 'present' : 'missing',
                'state' => $request->input('state'),
                'all' => $request->all(),
            ]);

            $githubUser = Socialite::driver('github')->stateless()->user();
            Log::info('GitHub User Data', (array)$githubUser);

            $user = User::where('github_id', $githubUser->id)->first();

            if (!$user) {
                // New user: set role to 3
                $user = User::create([
                    'github_id' => $githubUser->id,
                    'name' => $githubUser->name,
                    'email' => $githubUser->email ?? ($githubUser->nickname . '@github.com'),
                    'github_token' => $githubUser->token,
                    'github_refresh_token' => $githubUser->refreshToken,
                    'profile_image' => $githubUser->avatar,
                    'role' => 3,
                    'dev_role' => 'user',
                    'company_id' => null,
                ]);
            } else {
                $user->update([
                    'name' => $githubUser->name,
                    'email' => $githubUser->email ?? ($githubUser->nickname . '@github.com'),
                    'github_token' => $githubUser->token,
                    'github_refresh_token' => $githubUser->refreshToken,
                    'profile_image' => $githubUser->avatar,
                ]);
            }

            Auth::login($user);
            $token = $user->createToken('auth_token')->plainTextToken;

            $userData = urlencode(json_encode([
                'id' => $githubUser->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, 
                'profile_image' => $user->profile_image,
                'dev_role' => $user->dev_role,
                'company_id' => $user->company_id,
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
