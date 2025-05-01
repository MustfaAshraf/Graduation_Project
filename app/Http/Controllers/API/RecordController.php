<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class RecordController extends Controller
{

    public function store(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            $data = [
                'msg' => 'Invalid token, User Not Found'
            ];
            return response()->json($data, 401);
        }

        try {
            $request->validate([
                'payment_receipt' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $imgUrl = null;

        if ($request->hasFile('payment_receipt')) {
            $img = $request->file('payment_receipt'); 
            $imgName = rand() . time() . "." . $img->extension(); 
            $destinationPath = public_path('receipts'); 
            $img->move($destinationPath, $imgName);
            $imgUrl = url('receipts/' . $imgName);
        }

        Record::create([
            'student_name' => $user->name,
            'academic_year' => $user->semester,
            'receipt' => $imgName
        ]);

        // Store the request in the requests table
        DB::table('requests')->insert([
            'user_id' => $user->id,
            'type_en' => 'Housing Request',
            'type_ar' => 'طلب السكن الجامعي',
            'status' => 'Pending',
            'message_en' => 'Your request is under review',
            'message_ar' => 'طلبك قيد المراجعة',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $data = [
            'msg' => 'Request sent successfully',
            'data' => [
                'Student_Name' => $user->name,
                'Academic_Year' => $user->semester,
                'Receipt' => $imgUrl
            ]
        ];
        return response()->json($data, 200);
    }

    public function index(): JsonResponse
    {
        try {
            $records = Record::all()->map(function ($record) {
                return [
                    'id' => $record->id,
                    'student_name' => $record->student_name,
                    'academic_year' => $record->academic_year,
                    'receipt' => url('receipts/' . $record->receipt),
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $records
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve housing requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
