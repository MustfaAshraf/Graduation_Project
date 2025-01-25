<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class course extends Model
{
    use HasFactory;
    protected $fillable = [
        'title_en',
        'description_en',
        'instructor_en',
        'instructor_description_en',
        'title_ar',
        'description_ar',
        'instructor_ar',
        'instructor_description_ar',
        'image',
        'price',
        'ratings_count',
        'ratings_sum',
    ];
    
}
