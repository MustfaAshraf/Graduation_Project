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
            $data = [
                'msg' => 'No courses available',
                'status' => 404,
            ];
        } else {
            $data = [
                'msg' => 'All courses available',
                'status' => 200,
                'data' => $courses
            ];
        }
    
        return response()->json($data);
    }

    public function addRating(Request $request)
    {
        $course = Course::find($request->id);

        if (!$course) {
            return response()->json([
                'msg' => 'Course not found',
                'status' => 404
            ]);
        }

        $validatedData = $request->validate([
            'id' => 'required|numeric',
            'rating' => 'required|numeric|min:0|max:5',
        ]);

        $course->ratings_sum += $validatedData['rating'];
        $course->ratings_count += 1;
        $course->save();

        return response()->json([
            'msg' => 'Rating added successfully',
            'status' => 200,
        ]);
    }


}