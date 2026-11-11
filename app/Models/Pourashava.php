<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pourashava extends Model
{
    protected $table = 'pourashavas';

    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function pourashava_wards()
    {
        return $this->hasMany(PourashavaWard::class, 'pourashava_id', 'id');
    }
}
