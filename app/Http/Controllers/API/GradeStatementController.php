<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GradeStatement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeStatementController extends Controller
{
    public function getGradeStatement(Request $request): JsonResponse
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid token, User not found'
            ], 401);
        }

        try {
            $request->validate([
                'purpose' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors()
            ], 422);
        }

        // Store the request in grade_statements table
        $gradeStatement = GradeStatement::create([
            'name' => $user->name,
            'semester' => $user->semester,
            'university_id' => $user->university_id,
            'purpose' => $request->purpose,
        ]);

        // Store the request in requests table
        DB::table('requests')->insert([
            'user_id' => $user->id,
            'type_en' => 'Grade Statement Request',
            'type_ar' => 'طلب بيان درجات',
            'status' => 'Pending',
            'message_en' => 'Your request is under review',
            'message_ar' => 'طلبك قيد المراجعة',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'msg' => 'Request submitted successfully',
            'data' => $gradeStatement
        ], 200);
    }
}
