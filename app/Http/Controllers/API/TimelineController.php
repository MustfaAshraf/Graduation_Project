<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimelineController extends Controller
{
    public function CreateOrUpdateTimeline(Request $request)
    {
        try{
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

        // Only one timeline row will exist, so we update the first row or create one
        $timeline = Timeline::first();
        if ($timeline) {
            $timeline->update($request->all());
            $msg = 'Timeline updated successfully';
        } else {
            $timeline = Timeline::create($request->all());
            $msg = 'Timeline created successfully';
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
