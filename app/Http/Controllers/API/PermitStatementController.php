<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PermitStatement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PermitStatementController extends Controller
{
    public function getPermitStatement(Request $request): JsonResponse
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
        $gradeStatement = PermitStatement::create([
            'name' => $user->name,
            'semester' => $user->semester,
            'university_id' => $user->university_id,
            'purpose' => $request->purpose,
        ]);

        // Store the request in requests table
        DB::table('requests')->insert([
            'user_id' => $user->id,
            'type_en' => 'Permition Request',
            'type_ar' => 'طلب إفاده',
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

    public function index(): JsonResponse
    {
        try {
            $permitStatements = PermitStatement::all()->map(function ($statement) {
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

            if ($permitStatements->isEmpty()) {
                return response()->json([
                    'msg' => 'No permit statement requests found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'msg' => 'Permit statement requests retrieved successfully',
                'data' => $permitStatements
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => $e->getMessage()
            ], 422);
        }
    }
    public function destroy($id): JsonResponse
    {
        $permitStatement = PermitStatement::find($id);

        if (!$permitStatement) {
            return response()->json([
                'msg' => 'Permit statement not found'
            ], 404);
        }

        $permitStatement->delete();

        return response()->json([
            'msg' => 'Permit statement deleted successfully'
        ], 200);
    }

}
