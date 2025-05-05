<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use GrahamCampbell\GitHub\Facades\GitHub;

class GithubAuthController extends Controller
{
    public function redirectToGithub()
    {
        $state = Str::random(40);
        // Store state in cache with a 5-minute expiration
        Cache::put('github_state:' . $state, true, now()->addMinutes(5));
        
        Log::info('Generated GitHub state token', ['state' => $state]);

        $query = http_build_query([
            'client_id' => config('services.github.client_id'),
            'redirect_uri' => config('services.github.redirect'),
            'scope' => 'user repo',
            'state' => $state,
        ]);

        return response()->json([
            'url' => 'https://github.com/login/oauth/authorize?' . $query
        ]);
    }

    public function handleGithubCallback(Request $request)
    {
        $state = $request->get('state');
        $code = $request->get('code');
        
        Log::info('Handling GitHub callback', [
            'state' => $state,
            'code_exists' => !empty($code),
            'state_in_cache' => Cache::has('github_state:' . $state)
        ]);
        
        if (!$state || !Cache::has('github_state:' . $state)) {
            Log::error('Invalid state parameter', [
                'state' => $state,
                'cache_exists' => Cache::has('github_state:' . $state)
            ]);
            return response()->json(['error' => 'Invalid state parameter'], 400);
        }

        // Remove the state from cache after verification
        Cache::forget('github_state:' . $state);

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post('https://github.com/login/oauth/access_token', [
                'client_id' => config('services.github.client_id'),
                'client_secret' => config('services.github.client_secret'),
                'code' => $request->code,
                'redirect_uri' => config('services.github.redirect'),
            ]);

            Log::info('GitHub token response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json()
            ]);

            if (!$response->successful()) {
                Log::error('Failed to get GitHub access token', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                return response()->json(['error' => 'Failed to get access token'], 400);
            }

            $responseData = $response->json();
            if (!isset($responseData['access_token'])) {
                Log::error('No access token in response', [
                    'response' => $responseData
                ]);
                return response()->json(['error' => 'No access token in response'], 400);
            }

            $accessToken = $responseData['access_token'];

            // Get user information using GitHub API directly
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ])->get('https://api.github.com/user');

            if (!$userResponse->successful()) {
                Log::error('Failed to get GitHub user data', [
                    'status' => $userResponse->status(),
                    'body' => $userResponse->json()
                ]);
                return response()->json(['error' => 'Failed to get user data'], 400);
            }

            $githubUser = $userResponse->json();

            if (!isset($githubUser['id'])) {
                Log::error('Invalid GitHub user response', [
                    'response' => $githubUser
                ]);
                return response()->json(['error' => 'Invalid GitHub user data'], 400);
            }

            Log::info('Successfully retrieved GitHub user', [
                'github_id' => $githubUser['id'],
                'login' => $githubUser['login']
            ]);

            // Create or update user
            $user = User::updateOrCreate(
                ['github_id' => $githubUser['id']],
                [
                    'name' => $githubUser['name'] ?? $githubUser['login'] ?? 'Unknown User',
                    'email' => $githubUser['email'] ?? null,
                    'github_token' => $accessToken,
                    'github_refresh_token' => null,
                ]
            );

            // Create a Sanctum token for API authentication
            $token = $user->createToken('github-auth')->plainTextToken;

            Auth::login($user);

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'github_id' => $user->github_id,
                    'is_super_admin' => $user->is_super_admin ?? false,
                ],
                'github_user' => $githubUser
            ]);
        } catch (\Exception $e) {
            Log::error('Authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Authentication failed',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getAuthenticatedUser()
    {
        try {
            $github = GitHub::connection();
            $github->authenticate(Auth::user()->github_token, null, 'http_token');
            $githubUser = $github->me()->show();

            return response()->json([
                'user' => Auth::user(),
                'github_user' => $githubUser
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get authenticated user', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
