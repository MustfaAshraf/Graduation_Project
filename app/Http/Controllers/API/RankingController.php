<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RankingController extends Controller
{
    public function Ranking(Request $request): JsonResponse
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid token, User not found'
            ], 401);
        }

        $students = User::where('semester', $user->semester)
        ->whereNotNull('gpa')
        ->orderByDesc('gpa')
        ->get();    

        $rank = $students->search(fn($s) => $s->id === $user->id) + 1;
        $totalStudents = $students->count();

        return response()->json([
            'name' => $user->name,
            'semester' => $user->semester,
            'department' => $user->department,
            'university_id' => $user->university_id,
            'gpa' => $user->gpa,
            'rank' => $rank,
            'total_students' => $totalStudents
        ], 200);
    }
}
