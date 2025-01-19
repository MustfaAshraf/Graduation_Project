<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {

        $token = str_replace('Bearer ', '', $request->header('Authorization'));

        $user = User::where('token', $token)->first();
        
        if (!$user) {
            $data = [
                'msg' => 'Invalid token, User Not Found'
            ];
            return response()->json($data,401);
        }

        $user->token = null;
        $user->save();

        $data = [
            'msg' => 'Logged Out Successfully',
        ];
        return response()->json($data,200);
    }
}
