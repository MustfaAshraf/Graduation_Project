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
    public function fetchRequests(Request $request)
    {
        $token = $request->header('Authorization');

        // If no token is provided, return all requests (for admin dashboard)
        if (!$token) {
            $allRequests = DB::table('requests')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($allRequests->isEmpty()) {
                return response()->json([
                    'msg' => 'No requests found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'msg' => 'All Requests',
                'data' => $allRequests
            ], 200);
        }

        // Handle cases where a token is provided
        $token = str_replace('Bearer ', '', $token);
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid token, User not found'
            ], 401);
        }

        $existingRequests = DB::table('requests')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($existingRequests->isEmpty()) {
            return response()->json([
                'msg' => 'No requests found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'msg' => 'Your Requests',
            'data' => $existingRequests
        ], 200);
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
           $message_en = 'Your request has been approved';
           $message_ar = 'طلبك تم الموافقة عليه';
        }else if($request->status == 'Rejected'){
            $message_en = 'Your request has been rejected';
            $message_ar = 'طلبك تم رفضه';
        }

        // Update the request status and message
        DB::table('requests')
            ->where('id', $request->request_id)
            ->update([
                'status' => $request->status,
                'message_en' => $message_en,
                'message_ar' => $message_ar,
                'completed_at' => now()
            ]);

        // Return a success response
        return response()->json([
            'msg' => 'Request is completed successfully',
            'data' => [
                'request_id' => $request->request_id,
                'type_en' => $requestRecord->type_en,
                'type_ar' => $requestRecord->type_ar,
                'status' => $request->status,
                'message_en' => $message_en,
                'message_ar' => $message_ar,
                'completed_at' => now()
            ]
        ], 200);
    }
    public function getWeeklyRequestsStatus()
{
    // Fetch request statistics grouped by the day of the week
    $weeklyStatus = DB::table('requests')
        ->select(
            DB::raw('DAYNAME(created_at) as day_name'),
            DB::raw('SUM(CASE WHEN status = "Approved" THEN 1 ELSE 0 END) as approved_count'),
            DB::raw('SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) as pending_count'),
            DB::raw('COUNT(*) as total_requests')
        )
        ->groupBy(DB::raw('DAYNAME(created_at)'))
        ->get()
        ->keyBy('day_name');

    // Define the order of days in the week
    $daysOfWeek = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

    $result = [];

    // Ensure all days are included, even if there are no requests for that day
    foreach ($daysOfWeek as $day) {
        $result[] = [
            'day_name' => $day,
            'approved_count' => $weeklyStatus[$day]->approved_count ?? 0,
            'pending_count' => $weeklyStatus[$day]->pending_count ?? 0,
            'total_requests' => $weeklyStatus[$day]->total_requests ?? 0,
        ];
    }

    // Return the formatted response
    return response()->json([
        'msg' => 'Weekly Requests Statistics',
        'data' => $result
    ], 200);
}

}
