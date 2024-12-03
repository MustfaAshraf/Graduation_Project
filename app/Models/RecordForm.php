<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecordForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'id','user_id','payment_receipt',
    ];
    protected $casts = [
        'payment_receipt' => 'binary',
    ];

}
