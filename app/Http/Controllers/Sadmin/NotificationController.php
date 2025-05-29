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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $fcm_token = $request->fcm_token;
        Log::info('reuest', [$request->all()]);

        $result = DB::table('fcm_tokens')->updateOrInsert(
            ['token' => $fcm_token],
            ['user_id' => Auth::id()]
        );

        if ($result) {
            return res(true, [], ['FCM token updated successfully']);
        } else {
            return res(false, [], ['Failed to update FCM token']);
        }
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
                ->toToken('token', $token));
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
