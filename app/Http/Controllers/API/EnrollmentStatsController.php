<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Request;

class EnrollmentStatsController extends Controller
{
    public function getRequestsCount()
    {
        // Count complete requests
        $completeCount = Request::where('status', 'Approved')->count();
        
        // Count waiting/incomplete requests
        $waitingCount = Request::where('status', 'Pending')->count();
        
        return response()->json([
            'msg' => 'Enrollment request counts retrieved successfully',
            'data' => [
                'complete_requests' => $completeCount,
                'waiting_requests' => $waitingCount
            ]
        ], 200);
    }
}
