<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';

    protected $guarded = [];
    public $timestamps = false;
    public $incrementing = false;   // IDs are 1..8, not auto-increment
    protected $keyType = 'int';

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function upazilas()
    {
        return $this->hasMany(Upazila::class);
    }

    public function cityCorporations()
    {
        return $this->hasMany(CityCorporation::class);
    }

    public function pourashavas()
    {
        return $this->hasMany(Pourashava::class);
    }
}
