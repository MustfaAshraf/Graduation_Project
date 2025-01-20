<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'course_id',
        'rating',
        'created_at',
        'updated_at'
    ];
}
