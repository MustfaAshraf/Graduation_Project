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
                'device_token' => 'nullable|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:6',
                'role' => 'nullable|in:0,1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

        $token = Str::random(60);
        $otp = mt_rand(100000, 999999); // Generates a 6-digit numeric OTP

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(3),
            'token' => $token,
            'device_token' => $request->device_token,
            'is_verified' => false,
            'role' => $request->role ?? 0,
        ]);

        Mail::to($user->email)->send(new OTPMail($otp));

        $data = [
            'msg' => 'Registered successfully',
            'data' => [
                'email' => $user->email,
                'OTP' => $user->otp_code,
                'Token' => $token,
                'Device_Token' => $user->device_token,
            ]
        ];
        return response()->json($data, 200);
    }


    public function verifyOTP(Request $request)
    {
        try{
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);
        } catch (ValidationException $e) {
            return response()->json([
                'msg' => $e->errors(),
            ], 422);
        }

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
