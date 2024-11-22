<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\OTPMail;
use App\Models\User;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);

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
            'status' => 200,
            'data' => [
                'email' => $user->email,
                'OTP' => $user->otp_code,
                'Token' => $token
            ]
        ];
        return response()->json($data);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('otp_code', $request->otp)->first();

        if (!$user) {
            $data = [
                'msg' => 'Invalid OTP',
                'status' => 400
            ];
            return response()->json($data);
        }

        if (Carbon::now()->greaterThan($user->otp_expires_at)) {
            $data = [
                'msg' => 'OTP Has Expired',
                'status' => 400
            ];
            return response()->json($data);
        }

        $user->update([
            'is_verified' => true,
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        $data = [
            'msg' => 'Account verified successfully',
            'status' => 200
        ];
        return response()->json($data);
    }
}
