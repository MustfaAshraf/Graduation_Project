<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'complaint',
        'response',
        'status',
        'response_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
