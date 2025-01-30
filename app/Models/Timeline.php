<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_study',
        'end_registration',
        'quiz_1',
        'mid_exam',
        'quiz_2',
        'oral_practical_exams',
        'end_study',
        'start_final_exams',
        'end_final_exams'
    ];
}
