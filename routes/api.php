<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogoutController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\GetProfile;

use App\Http\Controllers\Api\RecordController;
use App\Http\Controllers\API\CourseImagesController; 


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

# Register & Verify Account
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-otp', [RegisterController::class, 'verifyOTP']);

# Login & Reset Password
Route::put('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [LoginController::class, 'sendResetLink']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);

# Users Data
Route::post('/data', [UserController::class, 'storeUserData']);

# Logout
Route::post('/logout', [LogoutController::class, 'logout']);

# Courses
Route::get('/all-courses', [CourseController::class, 'show']);
Route::post('/rating', [CourseController::class, 'addRating']);
Route::post('/course', [CourseController::class, 'showCourse']);

// User profile
Route::post('/user-data', [GetProfile::class, 'getUserInfo']);

# Record
Route::post('/university-requests', [RecordController::class, 'store']);

#CoursesImages
Route::post('/images/add', [CourseImagesController::class, 'uploadImage']);
Route::get('/all-images', [CourseImagesController::class, 'getImages']);
