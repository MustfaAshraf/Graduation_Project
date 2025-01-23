<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Mail\OTPMail;
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
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        $msg = 'Welcome, you are logged in';

        if (!$user) {
            $data = [
                'msg' => 'Account not registered, register first',
            ];
            return response()->json($data, 451);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $data = [
                'msg' => 'Invalid Password',
            ];
            return response()->json($data, 401);
        }

        // Check if the account is verified
        if (!$user->is_verified) {
            // Generate a new OTP
            $otp = Str::random(6);
            $user->otp_code = $otp;
            $user->otp_expires_at = Carbon::now()->addMinutes(3);
            $user->save();

            // Send the OTP via email
            Mail::to($user->email)->send(new OTPMail($otp));

            $msg = 'Account not verified, OTP sent to your email';
        }

        $is_completed = 1;

        if (is_null($user->name) || is_null($user->department) || is_null($user->national_id)) {
            $is_completed = 0;
        }

        // Generate a new token for the user
        $token = Str::random(60);
        $user->token = $token;
        $user->save();

        // Successful login response
        $data = [
            'msg' => $msg,
            'token' => $token,
            'Is_Completed' => $is_completed,
            'is_verified' => $user->is_verified,
            'data' => new UserResource($user),
        ];
        return response()->json($data, 200);
    }

    public function sendResetLink(Request $request)
    {
        try{
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
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
                'msg' => $e->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'msg' => 'User not found',
            ], 451);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'msg' => 'Password reset successfully',
        ], 200); // Success
    }

}
