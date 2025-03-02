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
        'image','name','email', 'password','semester','department','gpa','university_id', 'national_id', 'otp_code', 'otp_expires_at', 'is_verified' , 'token', 'device_token'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        //
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'device_token', 'device_token');
    }
}
