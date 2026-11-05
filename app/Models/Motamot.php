<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motamot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
    ];
}
