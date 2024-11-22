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
                'msg' => 'Token Not Valid',
                'status' => 401
            ];
            return response()->json($data);
        }

        $user->token = null;
        $user->save();

        $data = [
            'msg' => 'Logged Out Successfully',
            'status' => 200
        ];
        return response()->json($data);
    }
}
