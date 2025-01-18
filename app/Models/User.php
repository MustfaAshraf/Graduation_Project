<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'image','name','email', 'password','semester','department','gpa','university_code', 'national_id', 'otp_code', 'otp_expires_at', 'is_verified' , 'token'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        //
    ];
}
