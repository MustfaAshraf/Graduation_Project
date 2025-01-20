<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\course;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function show()
    {
        $courses = course::all();

        if ($courses->isEmpty()) {
            return response()->json([
                'msg' => 'No Courses Available',
            ], 204);
        } else {
            return response()->json([
                'msg' => 'All courses available',
                'data' => CourseResource::collection($courses)
            ], 200);
        }
    }

    public function addRating(Request $request)
    {
        $course = Course::find($request->id);

        if (!$course) {
            return response()->json([
                'msg' => 'Course Not Found',
            ], 204);
        }

        try{
        $validatedData = $request->validate([
            'id' => 'required|numeric',
            'rating' => 'required|numeric|min:0|max:5',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

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
            ],451);
        }
        return response()->json([
            'msg' => 'Course found',
            'data' => new CourseResource($course)
        ],200);
    }

    public function addCourse(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'instructor' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if($request->hasFile('image')) {

            $img = $request->file('image'); 
            $imgName = rand() . time() . "." . $img->extension(); 
            $destinationPath = public_path('courses_imgs'); 
            $img->move($destinationPath, $imgName);
        }

        $course = course::create([
            'title' => $validatedData['title'],
            'image' => $imgName,
            'instructor' => $validatedData['instructor'],
            'price' => $validatedData['price'],
        ]);

        return response()->json([
            'msg' => 'Course added successfully',
            'data' => new CourseResource($course)
        ], 200);
    }


}