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
        try {
            $request->validate([
                'device_token' => 'required|string|exists:users,device_token',
                'title' => 'required|string',
                'body' => 'required|string',
                'data' => 'required|array',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors()
            ], 422);
        }

        try {
            $this->firebaseService->sendNotification(
                $request->device_token,
                $request->title,
                $request->body,
                $request->data
            );

            return response()->json([
                'msg' => 'Notification sent to user successfully.'
            ], 200);
        } catch (NotFound $e) {
            // Handle invalid Firebase token
            Log::warning("Invalid Firebase token: {$request->device_token}");

            return response()->json([
                'msg' => 'Failed to send notification. Invalid device token.'
            ], 400);
        } catch (\Exception $e) {
            Log::error("Unexpected error: " . $e->getMessage());

            return response()->json([
                'msg' => 'Unexpected server error while sending notification.'
            ], 500);
        }
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

        // Get distinct non-null device tokens
        $deviceTokens = User::whereNotNull('device_token')
            ->pluck('device_token')
            ->unique()
            ->values();

        $invalidTokens = [];
        $successCount = 0;

        foreach ($deviceTokens as $token) {
            try {
                $this->firebaseService->sendNotification(
                    $token,
                    $request->input('title'),
                    $request->input('body'),
                    $request->input('data', [])
                );
                $successCount++;
            } catch (NotFound $e) {
                $invalidTokens[] = $token;
                Log::warning("Invalid Firebase token: {$token}");
            } catch (\Exception $e) {
                Log::error("Unexpected error sending notification to token {$token}: {$e->getMessage()}");
            }
        }

        return response()->json([
            'msg' => 'Notifications sent to all unique devices successfully.',
            'notified_count' => $successCount,
            'invalid_token_count' => count($invalidTokens),
            'invalid_tokens' => $invalidTokens, // Optional, remove if too large
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

        // Retrieve notifications (cloned before update)
        $notifications = Notification::where('device_token', $user->device_token)
                                    ->orderBy('created_at', 'desc')
                                    ->paginate(10);

        if ($notifications->isEmpty()) {
            return response()->json([
                'msg' => 'No notifications found for this user',
                'notifications' => []
            ], 200);
        }

        // Clone notifications before updating
        $responseNotifications = clone $notifications;

        // Mark unread ones as read (after cloning to preserve original states)
        Notification::where('device_token', $user->device_token)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'msg' => 'User notifications retrieved successfully',
            'notifications' => $responseNotifications
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

        $notification->is_read = true;
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

    public function deleteAllNotifications(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid token, User not found'
            ], 401);
        }

        Notification::where('device_token', $user->device_token)->delete();

        return response()->json([
            'msg' => 'All notifications deleted successfully'
        ], 200);
    }
    public function deleteNotification(Request $request)
    {
        $notification = Notification::find($request->id);

        if (!$notification) {
            return response()->json([
                'msg' => 'Notification not found'
            ], 401);
        }

        $notification->delete();

        return response()->json([
            'msg' => 'Notification deleted successfully'
        ], 200);
    }


}
