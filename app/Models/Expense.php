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
        '1st_term',
        '2nd_term',
        '3rd_term',
        '4th_term',
        '5th_term',
        '6th_term',
        '7th_term',
        '8th_term',
        'created_at',
        'updated_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
