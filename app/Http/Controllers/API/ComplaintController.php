<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Complaint;
use App\Models\User;

class ComplaintController extends Controller
{
    public function store(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        // Check if the user exists
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
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
            'message' => 'Complaint submitted successfully',
            'data' => $complaint,
        ], 201);
    }

    public function index()
    {
        // Retrieve all complaints ordered by latest
        $complaints = Complaint::latest()->get();
        return response()->json($complaints);
    }

    public function reply(Request $request, $id)
    {
        // Validate the response input
        $request->validate([
            'response' => 'required|string',
        ]);

        // Find the complaint by ID
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json(['message' => 'Complaint not found'], 404);
        }

        // Update complaint with admin response and change status
        $complaint->response = $request->response;
        $complaint->status = 'approved';
        $complaint->response_time = now();
        $complaint->save();

        return response()->json([
            'message' => 'Complaint has been responded to and status updated',
            'data' => $complaint
        ]);
    }
    public function complaintsByUser($user_id)
    {
        $complaints = Complaint::where('user_id', $user_id)->latest()->get()->makeHidden(['user_id']);;

        if ($complaints->isEmpty()) {
            return response()->json(['message' => 'No complaints found for this user.'], 404);
        }

        return response()->json($complaints);
    }

}
