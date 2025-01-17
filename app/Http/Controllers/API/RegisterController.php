<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\OTPMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:6',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $token = Str::random(60);
        $otp = Str::random(6);

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(3),
            'token' => $token,
            'is_verified' => false,
        ]);

        Mail::to($user->email)->send(new OTPMail($otp));

        $data = [
            'msg' => 'Registered successfully',
            'data' => [
                'email' => $user->email,
                'OTP' => $user->otp_code,
                'Token' => $token
            ]
        ];
        return response()->json($data,200);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        // Check in users table
        $user = User::where('otp_code', $request->otp)->first();

        if ($user) {
            if (Carbon::now()->greaterThan($user->otp_expires_at)) {
                return response()->json([
                    'msg' => 'OTP has expired',
                ], 400); 
            }
            
            $user->update([
                'is_verified' => true,
                'otp_code' => null,
                'otp_expires_at' => null
            ]);

            return response()->json([
                'msg' => 'Account verified successfully',
            ], 200);
        }

        // Check in password_resets table
        $passwordReset = DB::table('password_resets')
            ->where('otp_code', $request->otp)
            ->first();

        if ($passwordReset) {
            if (Carbon::now()->greaterThan($passwordReset->otp_expires_at)) {
                return response()->json([
                    'msg' => 'OTP has expired',
                ], 400);
            }

            return response()->json([
                'msg' => 'OTP verified successfully for password reset',
            ], 200);
        }

        return response()->json([
            'msg' => 'Invalid OTP',
        ], 401);
    }

}
