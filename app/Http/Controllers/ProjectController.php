<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class ProjectController extends Controller
{
    public function createRepo(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'private' => 'boolean',
        ]);

        $name = $request->input('name');
        $description = $request->input('description', '');
        $private = $request->input('private', true);

        // Get the authenticated user's GitHub token
        $user = Auth::user();
        $token = $user->github_token;

        $client = new Client();
        try {
            $response = $client->post('https://api.github.com/user/repos', [
                'headers' => [
                    'Authorization' => "Bearer $token",
                    'Accept' => 'application/vnd.github+json',
                ],
                'json' => [
                    'name' => $name,
                    'description' => $description,
                    'private' => $private,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                $repoData = json_decode($response->getBody(), true);
                return response()->json(['message' => 'Repository created successfully!', 'repo' => $repoData], 201);
            } else {
                return response()->json(['error' => 'Failed to create repository.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('GitHub repo creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
} 