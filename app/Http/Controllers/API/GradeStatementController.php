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

    public function index()
    {
        try {
            $gradeStatements = GradeStatement::all()->map(function ($statement) {
                return [
                    'id' => $statement->id,
                    'name' => $statement->name,
                    'semester' => $statement->semester,
                    'university_id' => $statement->university_id,
                    'purpose' => $statement->purpose,
                    'created_at' => $statement->created_at,
                    'updated_at' => $statement->updated_at
                ];
            });

            if ($gradeStatements->isEmpty()) {
                return response()->json([
                    'msg' => 'No grade statement requests found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'msg' => 'Grade statement requests retrieved successfully',
                'data' => $gradeStatements
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => $e->getMessage()
            ], 422);
        }
    }
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:grade_statements,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors()
            ], 422);
        }

        $gradeStatement = GradeStatement::find($request->id);

        $gradeStatement->delete();

        return response()->json([
            'msg' => 'Grade statement deleted successfully'
        ], 200);
    }

}
