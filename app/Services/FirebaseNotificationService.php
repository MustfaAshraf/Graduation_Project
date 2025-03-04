<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = app('firebase');
        $this->messaging = $firebase->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body, $data = [])
    {
        $message = CloudMessage::new()
            ->withNotification(FirebaseNotification::create($title, $body))
            ->withData($data)
            ->toToken($deviceToken);

        $this->messaging->send($message);

        // Store the notification in the database
        $user = User::where('device_token', $deviceToken)->first();
        if ($user) {
            Notification::create([
                'device_token' => $deviceToken,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);
        }
    }
}