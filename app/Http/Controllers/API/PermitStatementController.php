<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PermitStatement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermitStatementController extends Controller
{
    public function getPermitStatement(Request $request): JsonResponse
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json(['msg' => 'Invalid token, User not found'], 401);
        }

        $name = $user->name ?? 'N/A';
        $semester = $user->semester ?? 'N/A';
        $university_code = $user->university_code ?? 'N/A';
        $purpose = $request->input('purpose') ?? 'N/A';

        $permitRequest = PermitStatement::create([
            'purpose' => $purpose
        ]);

        return response()->json([
            'name' => $name,
            'semester' => $semester,
            'university_code' => $university_code,
            'purpose of permission' => $purpose,
        ]);
    }
}
