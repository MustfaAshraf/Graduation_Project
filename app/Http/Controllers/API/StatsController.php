<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\FacultyMembers;
use App\Models\User;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function getCounts()
    {
        $data = [
            'users' => User::count(),
            'requests' => \App\Models\Request::count(),
            'faculty_members' => FacultyMembers::count(),
            'complaints' => Complaint::count(),
        ];

        return response()->json($data, 200);
    }
}