<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motamot extends Model
{
    protected $fillable = [
        'subject',
        'description',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
