<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regulation extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'regulation',
        'lectures_tables', 
        'academic_guide', 
        'teams_guide', 
        'postgraduate_guide', 
        'role',
        'created_at',
        'updated_at'
    ];
}
