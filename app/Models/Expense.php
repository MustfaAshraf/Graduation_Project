<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'first_term',
        'second_term',
        'third_term',
        'fourth_term',
        'fifth_term',
        'sixth_term',
        'seventh_term',
        'eighth_term',
        'created_at',
        'updated_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
