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
                'msg' => 'Invalid token, User Not Found'
            ], 401);
        } else {
            $imageUrl = $user->image ? url('images/' . $user->image) : null;

            return response()->json([
                'image' => $imageUrl,
                'name' => $user->name,
                'semester' => $user->semester,
                'department' => $user->department,
                'gpa' => $user->gpa,
                'university_id' => $user->university_id,
                'national_id' => $user->national_id,
            ], 200);
        }
    }



}