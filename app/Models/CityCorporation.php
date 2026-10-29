<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityCorporation extends Model
{
    protected $table = 'city_corporations';

    protected $guarded = [];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }
}
