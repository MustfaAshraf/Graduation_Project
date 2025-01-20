<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function show()
    {
        $courses = course::all();

        if ($courses->isEmpty()) {
            return response()->json([
                'msg' => 'No Courses Available',
            ], 200);
        } else {
            return response()->json([
                'msg' => 'All courses available',
                'data' => CourseResource::collection($courses)
            ], 200);
        }
    }

    public function addRating(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'id' => 'required|numeric',
                'rating' => 'required|numeric|min:0|max:5',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $course = Course::find($request->id);

        if (!$course) {
            return response()->json([
                'msg' => 'Course Not Found',
            ], 200);
        }

        $token = str_replace('Bearer ', '', $request->header('Authorization'));
    
        $user = User::where('token', $token)->first();

        if(!$user){
            $data = [
                'msg' => 'Invalid token, User not found'
            ];
            return response()->json($data,401);
        }

        $existingRating = DB::table('ratings')
        ->where('user_id', $user->id)
        ->where('course_id', $request->id)
        ->first();

        if ($existingRating) {
            return response()->json([
                'msg' => 'You have already rated this course',
            ], 409);
        }

        DB::table('ratings')->insert([
            'user_id' => $user->id,
            'course_id' => $request->id,
            'rating' => $request->rating,
            'created_at' => now(),
            'updated_at' => now(),
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
        try{
        $request->validate([
            'id' => 'required|numeric',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        
        $course = Course::find($request->id);
        if (!$course) {
            return response()->json([
                'msg' => 'Course not found'
            ],200);
        }

        $token = str_replace('Bearer ', '', $request->header('Authorization'));
    
        $user = User::where('token', $token)->first();

        if(!$user){
            $data = [
                'msg' => 'Invalid token, User not found'
            ];
            return response()->json($data,401);
        }

        $rating = DB::table('ratings')
        ->where('user_id', $user->id)
        ->where('course_id', $request->id)
        ->first();

        if (!$rating) {
            return response()->json([
                'msg' => 'No rating found for this course by the user',
            ], 451);
        }

        return response()->json([
            'msg' => 'Course found',
            'data' => new CourseResource($course),
            'user rating' => $rating
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
                'msg' => $e->errors(),
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