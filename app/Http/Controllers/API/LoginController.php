<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validate input data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if(!$user){
            $data = [
                'msg' => 'Not Registered, Register first',
                'status' => 400
            ];
            return response()->json($data);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $data = [
                'msg' => 'Invalid Credentials',
                'status' => 401
            ];
            return response()->json($data);
        }

        $is_completed = 1;

        if (is_null($user->image) || is_null($user->semester) || is_null($user->department)
                || is_null($user->gpa) || is_null($user->university_code)) {
                $is_completed = 0;
            }

        $token = Str::random(60);

        $user->token = $token;
        $user->save();

        // Successful login response
        $data = [
            'msg' => 'Welcome, you are logged in',
            'status' => 200,
            'token' => $token,
            'Is_Completed' => $is_completed,
            'data' => $user
        ];
        return response()->json($data);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = Str::random(6);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(3),
            'created_at' => now(),
        ]);

        Mail::to($request->email)->send(new ResetPasswordMail($otp));

        $data = [
            'msg' => 'OTP sent to your email',
            'status' => 200,
            'otp' => $otp
        ];
        return response()->json($data);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'otp' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $passwordReset = DB::table('password_resets')->where('otp_code', $request->otp)->first();

        if (!$passwordReset) {
            $data = [
                'msg' => 'Invalid OTP',
                'status' => 400
            ];
            return response()->json($data);
        }

        if (Carbon::now()->greaterThan($passwordReset->otp_expires_at)) {
            $data = [
                'msg' => 'OTP Has Expired',
                'status' => 400
            ];
            return response()->json($data);
        }

        $user = User::where('email', $passwordReset->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $passwordReset->email)->delete();

        $data = [
            'msg' => 'Password reset successfully',
            'status' => 200
        ];
        return response()->json($data);
    }
}
