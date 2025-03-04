<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentStatsController extends Controller
{
    /**
     * Get the count of complete and incomplete enrollment requests
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRequestsCount()
    {
        // Count complete requests
        $completeCount = Enrollment::where('status', 'complete')->count();
        
        // Count waiting/incomplete requests
        $waitingCount = Enrollment::where('status', 'waiting')->count();
        
        return response()->json([
            'msg' => 'Enrollment request counts retrieved successfully',
            'data' => [
                'complete_requests' => $completeCount,
                'waiting_requests' => $waitingCount
            ]
        ], 200);
    }
}
