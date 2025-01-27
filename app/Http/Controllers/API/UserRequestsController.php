<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Constraint\IsEmpty;

class UserRequestsController extends Controller
{
    public function fetchRequests(Request $request){
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            $data = [
                'msg' => 'Invalid token, User not found'
            ];
            return response()->json($data, 401);
        }

        $existingRequests = DB::table('requests')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

        if($existingRequests->isEmpty()){
            return response()->json([
                'msg' => 'No requests found',
                'data' => []
            ], 200);
        } else {
            return response()->json([
                'msg' => 'Your Requests',
                'data' => $existingRequests
            ], 200);
        }
    }

    public function updateRequestStatus(Request $request)
    {
        try{
        $request->validate([
            'request_id' => 'required|exists:requests,id',
            'status' => 'required|string|in:Pending,Approved,Rejected',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        // Fetch the request record
        $requestRecord = DB::table('requests')->where('id', $request->request_id)->first();

        if (!$requestRecord) {
            return response()->json([
                'msg' => 'Request not found',
                'data' => []
            ], 200);
        }

        if($request->status == 'Approved'){
           $message = 'Your request has been approved';
        }else if($request->status == 'Rejected'){
            $message = 'Your request has been rejected';
        }

        // Update the request status and message
        DB::table('requests')
            ->where('id', $request->request_id)
            ->update([
                'status' => $request->status,
                'message' => $message,
                'completed_at' => now()
            ]);

        // Return a success response
        return response()->json([
            'msg' => 'Request is completed successfully',
            'data' => [
                'request_id' => $request->request_id,
                'status' => $request->status,
                'message' => $message,
                'completed_at' => now()
            ]
        ], 200);
    }
}
