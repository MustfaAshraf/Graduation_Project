<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyMembers extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name_en',
        'name_ar',
        'department',
        'created_at',
        'updated_at',
    ];
}
