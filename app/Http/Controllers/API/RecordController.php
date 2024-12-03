<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecordController extends Controller
{

    public function store(Request $request)

    {

        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        $user = User::where('token', $token)->first();

        $validatedData = $request->validate([
            'payment_receipt' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('payment_receipt')->store('payment_receipt', 'public');

        return response()->json([
            'message' => 'Data uploaded successfully',
            'student_name' => $user->name,
            'student_group' => $user->semester,
            'image_path' => $imagePath,
        ], 201);
    }
}
