<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Exception\Messaging\NotFound;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    # Send a notification to a specific user.
    public function sendToUser(Request $request)
    {
        try{
        $request->validate([
            'device_token' => 'required|exists:users',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'required|array',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors()
            ], 422);
        }

        $deviceToken = $request->device_token;

        $this->firebaseService->sendNotification(
            $deviceToken,
            $request->title,
            $request->body,
            $request->data
        );

        return response()->json([
            'msg' => 'Notification sent to user successfully.'
        ],200);
    }

    # Send a notification to all users.
    public function sendToAll(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'body' => 'required|string',
                'data' => 'nullable|array',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors()
            ], 422);
        }

        $users = User::whereNotNull('device_token')->get();
        $invalidTokens = [];

        foreach ($users as $user) {
            try {
                $this->firebaseService->sendNotification(
                    $user->device_token,
                    $request->input('title'),
                    $request->input('body'),
                    $request->input('data', [])
                );
            } catch (NotFound $e) {
                // Log invalid token and optionally remove it from DB
                $invalidTokens[] = $user->device_token;

                Log::warning("Invalid Firebase token for user ID {$user->id}: {$user->device_token}");

                // Optional: Remove the invalid token from the DB
                // $user->device_token = null;
                // $user->save();
            } catch (\Exception $e) {
                Log::error("Unexpected error sending notification to user ID {$user->id}: {$e->getMessage()}");
            }
        }

        return response()->json([
            'msg' => 'Notification sent to all users successfully.'
        ], 200);
    }

    # Get all notifications for the authenticated user.
    public function getUserNotifications(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid token, User not found'
            ], 401);
        }

        $notifications = Notification::where('device_token', $user->device_token)->paginate(10);

        if($notifications->isEmpty()) {
            return response()->json([
                'msg' => 'No notifications found for this user',
                'notifications' => []
            ], 401);
        }

        return response()->json([
            'msg' => 'User notifications retrieved successfully',
            'notifications' => $notifications
        ]);
    }

    # Mark a notification as read.
    public function markAsRead(Request $request)
    {
        $notification = Notification::find($request->id);

        if (!$notification) {
            return response()->json([
                'msg' => 'Notification not found'
            ], 401);
        }

        $notification->read_at = now();
        $notification->save();

        return response()->json([
            'msg' => 'Notification marked as read successfully'
        ], 200);
    }

    # Mark all notifications as read for an authenticated user.
    public function markAllAsRead(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid token, User not found'
            ], 401);
        }

        Notification::where('device_token', $user->device_token)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }


}
