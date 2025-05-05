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
            'org' => 'required|string',
        ]);

        $name = $request->input('name');
        $description = $request->input('description', '');
        $private = $request->input('private', true);
        $org = $request->input('org');

        // GitHub App credentials
        $appId = config('services.github.app_id');
        $privateKeyPath = config('services.github.private_key_path', storage_path('app/github-app.pem'));
        $privateKey = file_get_contents($privateKeyPath);

        // Generate JWT
        $payload = [
            'iat' => time(),
            'exp' => time() + (10 * 60),
            'iss' => $appId,
        ];
        $jwt = \Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');

        $client = new Client();
        try {
            // Get installation ID for the org
            $response = $client->get("https://api.github.com/orgs/{$org}/installation", [
                'headers' => [
                    'Authorization' => "Bearer $jwt",
                    'Accept' => 'application/vnd.github+json',
                ],
            ]);
            $installationId = json_decode($response->getBody(), true)['id'];

            // Create installation access token
            $response = $client->post("https://api.github.com/app/installations/{$installationId}/access_tokens", [
                'headers' => [
                    'Authorization' => "Bearer $jwt",
                    'Accept' => 'application/vnd.github+json',
                ],
            ]);
            $installationToken = json_decode($response->getBody(), true)['token'];

            // Create the repo in the org
            $response = $client->post("https://api.github.com/orgs/{$org}/repos", [
                'headers' => [
                    'Authorization' => "Bearer $installationToken",
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
            Log::error('GitHub org repo creation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
} 