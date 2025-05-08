<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpensesResource;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExpensesController extends Controller
{
    public function Upload(Request $request){

        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid Token, User Not Found'
            ], 401);
        }

        try{
        $request->validate([
            'term' => 'required|string|in:first_term,second_term,third_term,fourth_term,fifth_term,sixth_term,seventh_term,eighth_term',
            'receipt' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(), // Validation errors
            ], 422);
        }

        if ($request->hasFile('receipt')) {
            $img = $request->file('receipt'); 
            $imgName = rand() . time() . "." . $img->extension(); 
            $destinationPath = public_path('Expenses'); 
            $img->move($destinationPath, $imgName);
            $imgUrl = url('Expenses/' . $imgName);
            
            $expense = Expense::firstOrCreate(['user_id' => $user->id]);
            $expense->{$request->term} = $imgName;
        } else {
            return response()->json([
                'msg' => 'No image found in the request, Please upload your receipt',
            ], 451);
        }
        return response()->json([
            'msg' => 'Expense receipt uploaded successfully',
            'receipt_url' => $imgUrl,
        ], 200);
    }
    public function getUserExpenses(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'Invalid Token, User Not Found'
            ], 401);
        }

        $expenses = Expense::where('user_id', $user->id)->first();

        if (!$expenses) {
            return response()->json([
                'msg' => 'No expenses found for this user',
                'data' => []
            ], 200);
        }

        return response()->json([
            'msg' => 'Expenses retrieved successfully',
            'data' => new ExpensesResource($expenses)
        ], 200);
    }
    public function getAllExpenses()
    {
        $expenses = Expense::all();

        if ($expenses->isEmpty()) {
            return response()->json([
                'msg' => 'No expenses found in the system',
                'data' => []
            ], 200);
        }

        return response()->json([
            'msg' => 'All expenses retrieved successfully',
            'data' => ExpensesResource::collection($expenses)
        ], 200);
    }
}
