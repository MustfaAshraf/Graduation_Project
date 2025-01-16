<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function show()
    {
        $courses = CourseResource::collection(course::all());

        if ($courses->isEmpty()) {
            return response()->json([
                'msg' => 'No Courses Available',
            ], 404);
        } else {
            return response()->json([
                'msg' => 'All courses available',
                'data' => $courses
            ], 200);
        }
    }

    public function addRating(Request $request)
    {
        $course = Course::find($request->id);

        if (!$course) {
            return response()->json([
                'msg' => 'Course Not Found',
            ], 404);
        }

        $validatedData = $request->validate([
            'id' => 'required|numeric',
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        $course->ratings_sum += $validatedData['rating'];
        $course->ratings_count += 1;
        $course->save();

        return response()->json([
            'msg' => 'Rating added successfully'
        ],200);
    }

    public function showCourse(Request $request)
    {
        $course = Course::find($request->id);
        if (!$course) {
            return response()->json([
                'msg' => 'Course not found'
            ],404);
        }
        return response()->json([
            'msg' => 'Course found',
            'data' => new CourseResource($course)
        ],200);
    }

}