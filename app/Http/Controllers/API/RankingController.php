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
            return response()->json(['msg' => 'Invalid token, User not found'], 401);
        }

        $name = $user->name ?? 'N/A';
        $semester = $user->semester ?? 'N/A';
        $university_code = $user->university_code ?? 'N/A';
        $gpa = $user->gpa ?? 0;

        $students = User::where('semester', $user->semester)
            ->orderByDesc('gpa')
            ->get();

        $rank = $students->search(fn($s) => $s->id === $user->id) + 1;
        $totalStudents = $students->count();

        return response()->json([
            'name' => $name,
            'semester' => $semester,
            'university_code' => $university_code,
            'gpa' => $gpa,
            'rank' => $rank,
            'total_students' => $totalStudents
        ]);
    }
}
