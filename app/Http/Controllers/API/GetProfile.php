<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Ensure you import the User model

class GetProfile extends Controller
{


    public function getUserInfo(Request $request)
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        $user = User::where('token', $token)->first();
    
        if (!$user) {
            return response()->json([
                'msg' => 'Not Registered, Register first'
            ], 451);
        }
        else {
            return response()->json([
                'image' => $user->image,
                'name' => $user->name,
                'semester' => $user->semester,
                'gpa' => $user->gpa,
                'department' => $user->department,
                'university_code' => $user->university_code,
            ], 200);
        }
    }


}