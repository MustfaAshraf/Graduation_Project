<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regulation extends Model
{
    use HasFactory;
    protected $fillable = ['regulation', 'lectures_tables', 'academic_guide', 'teams_guide', 'postgraduate_guide', 'role'];
}
