<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\course;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function show()
    {
        $courses = course::all();

        if ($courses->isEmpty()) {
            return response()->json([
                'msg' => 'No Courses Available',
                'data' => []
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
        try {
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
                'data' => []
            ], 200);
        }

        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid token, User not found'
            ], 401);
        }

        // Check if the user has already rated the course
        $existingRating = DB::table('ratings')
            ->where('user_id', $user->id)
            ->where('course_id', $request->id)
            ->first();

        if ($existingRating) {
            // Update the existing rating
            DB::table('ratings')
                ->where('user_id', $user->id)
                ->where('course_id', $request->id)
                ->update([
                    'rating' => $validatedData['rating'],
                    'updated_at' => now(),
                ]);

            // Adjust the course's rating sum
            $course->ratings_sum = ($course->ratings_sum - $existingRating->rating) + $validatedData['rating'];
        } else {
            // Insert a new rating if not rated before
            DB::table('ratings')->insert([
                'user_id' => $user->id,
                'course_id' => $request->id,
                'rating' => $validatedData['rating'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Increase rating count
            $course->ratings_sum += $validatedData['rating'];
            $course->ratings_count += 1;
        }

        $course->save();

        return response()->json([
            'msg' => 'Rating added successfully'
        ], 200);
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
                'msg' => 'Course not found',
                'data' => []
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
                'msg' => 'Course found',
                'data' => new CourseResource($course),
                'user rating' => 0
            ],200);
        }

        return response()->json([
            'msg' => 'Course found',
            'data' => new CourseResource($course),
            'user rating' => $rating->rating
        ],200);
    }

    public function addCourse(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title_en' => 'required|string|max:255',
                'description_en' => 'required|string',
                'instructor_en' => 'required|string|max:255',
                'instructor_description_en' => 'required|string',
                'title_ar' => 'required|string|max:255',
                'description_ar' => 'required|string',
                'instructor_ar' => 'required|string|max:255',
                'instructor_description_ar' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'price' => 'required|numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $imgName = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('courses_imgs');
            $img->move($destinationPath, $imgName);
        }

        $course = Course::create([
            'title_en' => $validatedData['title_en'],
            'description_en' => $validatedData['description_en'],
            'instructor_en' => $validatedData['instructor_en'],
            'instructor_description_en' => $validatedData['instructor_description_en'],
            'title_ar' => $validatedData['title_ar'],
            'description_ar' => $validatedData['description_ar'],
            'instructor_ar' => $validatedData['instructor_ar'],
            'instructor_description_ar' => $validatedData['instructor_description_ar'],
            'image' => $imgName,
            'price' => $validatedData['price'],
        ]);

        // 🔔 Send notification to all users
        $users = User::whereNotNull('device_token')->pluck('device_token')->unique();

        foreach ($users as $deviceToken) {
            try {
                $this->firebaseService->sendNotification(
                    $deviceToken,
                    'New Course Available!',
                    'Check out our new course: ' . $course->title_en,
                    [
                        'course_id' => $course->id,
                        'title' => $course->title_en,
                        'price' => $course->price
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to send course notification to token: $deviceToken | Error: " . $e->getMessage());
            }
        }

        return response()->json([
            'msg' => 'Course added successfully',
            'data' => new CourseResource($course),
        ], 200);
    }

    public function updateCourse(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|numeric',
                'title_en' => 'string|max:255',
                'description_en' => 'string',
                'instructor_en' => 'string|max:255',
                'instructor_description_en' => 'string',
                'title_ar' => 'string|max:255',
                'description_ar' => 'string',
                'instructor_ar' => 'string|max:255',
                'instructor_description_ar' => 'string',
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'price' => 'numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $course = Course::find($request->id);

        if (!$course) {
            return response()->json([
                'msg' => 'Course not found',
                'data' => []
            ], 200);
        }

        // Handle image update if new image is provided
        if ($request->hasFile('image') && $request->file('image') !== null) {
            // Delete old image if exists
            if ($course->image) {
                $oldImagePath = public_path('courses_imgs/' . $course->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Upload new image
            $img = $request->file('image');
            $imgName = rand() . time() . "." . $img->extension();
            $destinationPath = public_path('courses_imgs');
            $img->move($destinationPath, $imgName);
            $validatedData['image'] = $imgName;
        } else {
            unset($validatedData['image']);
        }

        // Update only the fields that are present in the request
        $course->update(array_filter($validatedData, function ($value) {
            return $value !== null;
        }));

        return response()->json([
            'msg' => 'Course updated successfully',
            'data' => new CourseResource($course),
        ], 200);
    }

    public function deleteCourse(Request $request)
    {
        try {
            $validatedData = $request->validate([
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
                'msg' => 'Course not found',
                'data' => []
            ], 200);
        }

        // Delete course image if exists
        if ($course->image) {
            $imagePath = public_path('courses_imgs/' . $course->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete related ratings
        DB::table('ratings')->where('course_id', $request->id)->delete();

        // Delete the course
        $course->delete();

        return response()->json([
            'msg' => 'Course deleted successfully',
            'data' => []
        ], 200);
    }
}
