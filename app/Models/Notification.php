<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'id','device_token', 'title', 'title_en', 'body', 'body_en', 'type', 'data', 'read_at',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class, 'device_token', 'device_token');
    }
}
