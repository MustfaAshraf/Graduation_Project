<?php

use App\Http\Controllers\API\EnrollmentController;
use App\Http\Controllers\API\ExpensesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\LogoutController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\GetProfile;

use App\Http\Controllers\Api\RecordController;
use App\Http\Controllers\API\UserRequestsController;
use App\Http\Controllers\API\GradeStatementController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PermitStatementController;
use App\Http\Controllers\API\RankingController;
use App\Http\Controllers\API\RegulationFileController;
use App\Http\Controllers\API\TimelineController;
use App\Http\Controllers\API\CourseImagesController;
use App\Http\Controllers\API\EnrollmentStatsController;
use App\Http\Controllers\API\ComplaintController;
use App\Http\Controllers\API\FacultyMembersController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

# Register & Verify Account
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify-otp', [RegisterController::class, 'verifyOTP']);

# Login & Reset Password
Route::post('/login', [LoginController::class, 'login']);
Route::post('/forgot-password', [LoginController::class, 'sendResetLink']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);

# Users Data
Route::post('/data', [UserController::class, 'storeUserData']);
Route::post('/all-users', [UserController::class, 'getAllUsers']);
Route::post('/users/add', [UserController::class, 'addUser']);
Route::post('/users/update', [UserController::class, 'updateUser']);
Route::post('/users/delete', [UserController::class, 'deleteUser']);

# Logout
Route::post('/logout', [LogoutController::class, 'logout']);

# Courses
Route::get('/all-courses', [CourseController::class, 'show']);
Route::post('/rating', [CourseController::class, 'addRating']);
Route::post('/course/add', [CourseController::class, 'addCourse']);
Route::post('/course', [CourseController::class, 'showCourse']);
Route::post('/update-course', [CourseController::class, 'updateCourse']);
Route::post('/delete-course', [CourseController::class, 'deleteCourse']);

Route::post('/user-data', [GetProfile::class, 'getUserInfo']);

# Record
Route::post('/university-requests', [RecordController::class, 'store']);
Route::get('/housing-requests', [RecordController::class, 'index']);
Route::post('/requests/delete', [RecordController::class, 'destroy']);

#CoursesImages
Route::post('/images/add', [CourseImagesController::class, 'uploadImage']);
Route::get('/all-images', [CourseImagesController::class, 'getImages']);

#Enrollments
Route::post('/enroll', [EnrollmentController::class, 'store']);
Route::get('/enrollments', [EnrollmentController::class, 'index']);
Route::post('/enrollments/delete', [EnrollmentController::class, 'destroy']);

#User Requests
Route::post('/requests', [UserRequestsController::class, 'fetchRequests']);
Route::post('/all-requests', [UserRequestsController::class, 'fetchAllRequests']);
Route::post('/update-request', [UserRequestsController::class, 'updateRequestStatus']);

# Grade Request
Route::post('/grade-Request', [GradeStatementController::class, 'getGradeStatement']);
Route::get('/grade-statements', [GradeStatementController::class, 'index']);
Route::post('/grade-statements/delete', [GradeStatementController::class, 'destroy']);


# permission Request
Route::post('/permit-Request', [PermitStatementController::class, 'getPermitStatement']);
Route::get('/permit-statements', [PermitStatementController::class, 'index']);
Route::post('/permit-statements/delete', [PermitStatementController::class, 'destroy']);


# Ranking
Route::post('/ranking', [RankingController::class, 'Ranking']);

# Regulations
Route::post('/upload-file', [RegulationFileController::class, 'UploadFile']);
Route::post('/latest-file', [RegulationFileController::class, 'getLatestFile']);

# Timeline
Route::post('/create-or-update-timeline', [TimelineController::class, 'CreateOrUpdateTimeline']);
Route::get('/timeline', [TimelineController::class, 'getTimeline']);

# Notifications
Route::post('/notifications/send-to-user', [NotificationController::class, 'sendToUser']);
Route::post('/notifications/send-to-all', [NotificationController::class, 'sendToAll']);
Route::post('/notifications', [NotificationController::class, 'getUserNotifications']);
Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);
Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

# get requests
Route::get('/requests/weekly-status', [UserRequestsController::class, 'getWeeklyRequestsStatus']);

#Enrollment Statistics
Route::get('/enrollment-stats', [EnrollmentStatsController::class, 'getRequestsCount']);


# Complaints
Route::post('/write-complaint', [ComplaintController::class, 'store']);
Route::get('/complaints', [ComplaintController::class, 'index']);
Route::post('/reply-complaint', [ComplaintController::class, 'reply']);
Route::post('/user-complaints', [ComplaintController::class, 'complaintsByUser']);
Route::post('/complaint/update', [ComplaintController::class, 'update']);
Route::post('/complaint/delete', [ComplaintController::class, 'delete']);

# Faculty Members
Route::get('/faculty-members', [FacultyMembersController::class, 'AllMembers']);
Route::post('/faculty-members/add', [FacultyMembersController::class, 'store']);
Route::post('/faculty-members/update', [FacultyMembersController::class, 'update']);
Route::post('/faculty-members/delete', [FacultyMembersController::class, 'delete']);

# Expenses
Route::post('/expenses/add', [ExpensesController::class, 'Upload']);
Route::post('/user/expenses', [ExpensesController::class, 'getUserExpenses']);
Route::get('/all-expenses', [ExpensesController::class, 'getAllExpenses']);




