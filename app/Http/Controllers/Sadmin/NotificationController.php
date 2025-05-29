<?php

namespace App\Http\Controllers\Sadmin;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Http\Controllers\Controller;
use App\Models\fcm_tokens;
use App\Models\Notifications;
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
        Log::info('request', [$request->all()]);

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

        $user = User::find($request->user_id);
        $token = fcm_tokens::where('user_id', $user->id)->value('token');

        if (!$token) {
            return response()->json(['success' => false, 'data' => [], 'message' => ['No token found for user']]);
        }

        $message = CloudMessage::fromArray([
            'token' => $token,
            'notification' => [
                'title' => $request->title,
                'body' => $request->body,
            ],
        ]);

        try {
            Log::info('FCM: Attempting to send notification to user ID ' . $request->user_id, [
                'token' => $token,
                'title' => $request->title,
                'body' => $request->body
            ]);
            $factory = (new Factory)->withServiceAccount(env('FIREBASE_CREDENTIALS'));
            $factory->createMessaging()->send($message);
            Log::info('FCM: Notification sent successfully to user ID ' . $request->user_id);
            Notifications::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'body' => $request->body,
            ]);
            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Kreait\Firebase\Exception\Messaging\AuthenticationError $e) {
            Log::error('FCM: Authentication error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
                'token' => substr($token, 0, 10) . '...',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Authentication error', 'error' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            Log::error('FCM: Failed to send notification', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
                'token' => substr($token, 0, 10) . '...',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Failed to send notification', 'error' => $e->getMessage()], 500);
        }
    }
    



    public function getNotifications(Request $request)
    {
        if($request->read == 'false'){
            $notifications = Notifications::where('user_id', Auth::id())->where('read', false)->get();
            return res(true, $notifications, ['Notifications fetched successfully']);
        }else{
            $notifications = Notifications::where('user_id', Auth::id())->get();
            return res(true, $notifications, ['Notifications fetched successfully']);
        }
    }

    public function markAsRead(Request $request)
    {
        $notification = Notifications::find($request->id);
        $notification->read = true;
        $notification->save();
        return res(true, [], ['Notification marked as read']);
    }

    public function deleteNotification(Request $request)
    {
        $notification = Notifications::find($request->id);
        $notification->delete();
        return res(true, [], ['Notification deleted successfully']);
    }

}
