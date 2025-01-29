<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitStatement extends Model
{
    use HasFactory;

    protected $table = 'permit_statements';
    protected $fillable = ['name', 'semester', 'university_id', 'purpose'];
}
