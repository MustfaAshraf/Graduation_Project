<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeStatement extends Model
{
    use HasFactory;

    protected $table = 'grade_statements'; 

    protected $fillable = ['purpose'];
}
