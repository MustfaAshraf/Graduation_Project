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
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try{
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if(!$user){
            $data = [
                'msg' => 'Not Registered, Register first'
            ];
            return response()->json($data,204);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $data = [
                'msg' => 'Invalid Credentials'
            ];
            return response()->json($data,401);
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
            'token' => $token,
            'Is_Completed' => $is_completed,
            'data' => $user
        ];
        return response()->json($data,200);
    }

    public function sendResetLink(Request $request)
    {
        try{
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

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
            'otp' => $otp
        ];
        return response()->json($data,200);
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|confirmed|min:6',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'User not found',
            ], 204);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'msg' => 'Password reset successfully',
        ], 200); // Success
    }

}
