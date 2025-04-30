<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Request;

use function PHPUnit\Framework\isEmpty;

class EnrollmentStatsController extends Controller
{
    public function getRequestsCount()
    {
        $msg = 'Requests retrieved successfully';
        // Count complete requests
        $completeCount = Request::where('status', 'Approved')->count();

        // Count waiting/incomplete requests
        $waitingCount = Request::where('status', 'Pending')->count();

        if(isEmpty($completeCount) && isEmpty($waitingCount)){
            $msg = 'No requests found';
        }
        return response()->json([
            'msg' => $msg,
            'data' => [
                'Approved_Requests' => $completeCount ?? 0,
                'Pending_Requests' => $waitingCount ?? 0
            ]
        ], 200);
    }
}
