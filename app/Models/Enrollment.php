<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'national_id',
        'id_photo_f',
        'id_photo_b',
        'nomination_card_photo',
    ];
}
