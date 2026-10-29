<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Union extends Model
{
    protected $table = 'unions';

    protected $guarded = [];

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }
}
