<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporterType extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reporter_types' => 'array',
    ];
}
