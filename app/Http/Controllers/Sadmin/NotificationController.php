<?php

namespace App\Http\Controllers\Sadmin;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Http\Controllers\Controller;
use App\Models\fcm_tokens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    public function saveFcmToken(Request $request)
    {

        // $user = Auth::user();

        $request->validate([
            'token' => 'required|string',
            'device_id' => 'string|nullable',
            'platform' => 'string|nullable',
            'id' => 'required|exists:users,github_id',
        ]);
        $user_id = User::where('github_id', $request->id)->value('id');

        fcm_tokens::updateOrCreate(
            [
                'user_id' => $user_id,
                'token' => $request->token,
                'device_id' => $request->device_id,
                'platform' => $request->platform,
            ]
        );

        return response()->json(['message' => 'FCM token saved successfully'], 200);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $firebase = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $messaging = $firebase->createMessaging();

        Log::info('Firebase connection initialized');

        // Get the first token for the specific user
        $token = fcm_tokens::where('user_id', $request->user_id)->value('token');

        if (!$token) {
            Log::warning('No token found for user_id: ' . $request->user_id);
            return response()->json(['message' => 'No token available for this user'], 400);
        }

        Log::info('Sending notification to user', [
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body,
            'token' => $token,
        ]);

        try {
            $messaging->send(CloudMessage::new()
                ->withNotification(Notification::create($request->title, $request->body))
                ->withTarget('token', $token));
        } catch (\Exception $e) {
            $errorDetails = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'code' => $e->getCode(),
            ];
            if ($e instanceof \GuzzleHttp\Exception\RequestException && method_exists($e, 'getResponse')) {
                $errorDetails['response'] = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response';
            }
            Log::error('Error sending notification', $errorDetails);
            return response()->json(['message' => 'Failed to send notification'], 500);
        }

        return response()->json(['message' => 'Notification sent successfully']);
    }
}
