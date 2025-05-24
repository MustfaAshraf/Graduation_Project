<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ComplaintController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }
    
    public function store(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'msg' => 'Invalid Token, User Not Found'
            ], 401);
        }

        // Validate complaint input only
        $request->validate([
            'complaint' => 'required|string',
        ]);

        // Create the complaint and link it to the user
        $complaint = Complaint::create([
            'user_id' => $user->id,
            'complaint' => $request->complaint,
            'status' => 'pending',
        ]);

        return response()->json([
            'msg' => 'Complaint submitted successfully',
            'data' => $complaint,
        ], 200);
    }

    public function index()
    {
        // Retrieve all complaints ordered by latest
        $complaints = Complaint::latest()->get();
        if ($complaints->isEmpty()) {
            return response()->json([
                'msg' => 'No complaints found',
                'data' => [],
            ], 200);
        }
        return response()->json([
            'msg' => 'Complaints retrieved successfully',
            'data' => $complaints
        ],200);
    }

    public function reply(Request $request)
    {
        // Validate the input
        $request->validate([
            'id' => 'required|numeric',
            'response' => 'required|string',
        ]);

        // Find the complaint
        $complaint = Complaint::find($request->id);

        if (!$complaint) {
            return response()->json([
                'msg' => 'Complaint not found',
                'data' => [],
            ], 200);
        }

        // Update the complaint
        $complaint->response = $request->response;
        $complaint->status = 'approved';
        $complaint->response_time = now();
        $complaint->save();

        // Send notification to the user
        $user = User::find($complaint->user_id); // assuming user_id exists

        $title = "تم الرد علي الشكوي";
        $title_en = "Complaint Replied";
        $body = "يُرجى مراجعة الرد لمعرفة المزيد من التفاصيل";
        $body_en = "Please check the response for more details.";

        if ($user && $user->device_token) {
            try {
                $this->firebaseService->sendNotification(
                    $user->device_token,
                    $title,
                    $body,
                    [
                        'complaint_id' => $complaint->id,
                        'status' => 'approved'
                    ]
                );

            Notification::where('device_token', $user->device_token)->latest()->first()?->update([
                        'title_en' => $title_en,
                        'body_en' => $body_en,
                        'type' => '4'
                    ]);
            } catch (\Exception $e) {
                Log::error("Notification failed for user ID {$user->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'msg' => 'Complaint has been responded to and status updated',
            'data' => $complaint
        ], 200);
    }

    public function complaintsByUser(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        // Check if the user exists
        if (!$user) {
            return response()->json([
                'msg' => 'Invalid Token, User Not Found'
            ], 401);
        }

        $complaints = Complaint::where('user_id', $user->id)
        ->latest()
        ->get()
        ->makeHidden(['user_id']);

        if ($complaints->isEmpty()) {
            return response()->json(
                [
                    'msg' => 'No complaints found for this user.',
                    'data' => [],
                    ], 200);
        }

        return response()->json([
            'msg' => 'Complaints retrieved successfully',
            'national_id' => $user->national_id,
            'data' => $complaints
        ], 200);
    }

    public function delete(Request $request)
    {
        // Validate the complaint ID input
        $request->validate([
            'id' => 'required|numeric',
        ]);

        // Find the complaint by ID
        $complaint = Complaint::find($request->id);

        if (!$complaint) {
            return response()->json([
                'msg' => 'Complaint not found',
                'data' => [],
            ], 200);
        }

        // Delete the complaint
        $complaint->delete();

        return response()->json([
            'msg' => 'Complaint deleted successfully',
            'data' => [],
        ], 200);
    }

    public function update(Request $request)
    {
        try{
        $request->validate([
            'id' => 'required|exists:complaints,id',
            'response' => 'required|string',
            'status' => 'required|in:pending,approved'
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        } 
    
        $complaint = Complaint::find($request->id);
        
        $complaint->update([
            'response' => $request->response,
            'status' => $request->status,
            'response_time' => now()
        ]);
    
        return response()->json([
            'message' => 'Complaint updated successfully.',
            'data' => $complaint
        ]);
    }
}
