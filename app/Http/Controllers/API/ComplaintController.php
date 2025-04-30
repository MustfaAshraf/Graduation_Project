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
            return response()->json([
                'msg' => 'Invalid Token, User Not Found'
            ], 451);
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

    public function reply(Request $request, $id)
    {
        // Validate the response input
        $request->validate([
            'id' => 'required|numeric',
            'response' => 'required|string',
        ]);

        // Find the complaint by ID
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json([
                'msg' => 'Complaint not found',
                'data' => [],
                ], 200);
        }

        // Update complaint with admin response and change status
        $complaint->response = $request->response;
        $complaint->status = 'approved';
        $complaint->response_time = now();
        $complaint->save();

        return response()->json([
            'msg' => 'Complaint has been responded to and status updated',
            'data' => $complaint
        ],200);
    }
}
