<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Timeline;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TimelineController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function CreateOrUpdateTimeline(Request $request)
    {
        try {
            $request->validate([
                'start_study' => 'required|date',
                'end_registration' => 'required|date',
                'quiz_1' => 'required|date',
                'mid_exam' => 'required|date',
                'quiz_2' => 'required|date',
                'oral_practical_exams' => 'required|date',
                'end_study' => 'required|date',
                'start_final_exams' => 'required|date',
                'end_final_exams' => 'required|date',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $timeline = Timeline::first();
        if ($timeline) {
            $timeline->update($request->all());
            $msg = 'Timeline updated successfully';
            $notificationTitle = 'Timeline Updated';
            $notificationBody = 'The academic timeline has been updated. Please check the new dates.';
        } else {
            $timeline = Timeline::create($request->all());
            $msg = 'Timeline created successfully';
            $notificationTitle = 'New Academic Timeline';
            $notificationBody = 'A new academic timeline has been created. Check the schedule for important dates.';
        }

        // Send notification to all users with valid device_token
        $users = User::whereNotNull('device_token')->pluck('device_token')->unique();

        foreach ($users as $deviceToken) {
            try {
                $this->firebaseService->sendNotification(
                    $deviceToken,
                    $notificationTitle,
                    $notificationBody,
                    [
                       'type' => 'timeline'
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to the notification to token: $deviceToken | Error: " . $e->getMessage());
            }
        }

        return response()->json([
            'msg' => $msg,
            'timeline' => $timeline
        ], 200);
    }


    public function getTimeline()
    {
        $timeline = Timeline::first();

        if (!$timeline) {
            return response()->json([
                'msg' => 'No timeline found',
                'timeline' => []
            ], 200);
        }

        return response()->json([
            'msg' => 'Timeline retrieved successfully',
            'timeline' => $timeline
        ], 200);
    }
}
