<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = 'wards';

    protected $guarded = [];

    public function cityCorporation()
    {
        return $this->belongsTo(CityCorporation::class);
    }
}
