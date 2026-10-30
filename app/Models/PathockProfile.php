<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PathockProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'user_id' => 'integer',
        'division_id' => 'integer',
        'district_id' => 'integer',
        'upazilla_id' => 'integer',
        'union_id' => 'integer',
        'city_corporation_id' => 'integer',
        'ward_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function upazilla()
    {
        return $this->belongsTo(Upazila::class, 'upazilla_id', 'id');
    }

    public function union()
    {
        return $this->belongsTo(Union::class, 'union_id', 'id');
    }

    public function cityCorporation()
    {
        return $this->belongsTo(CityCorporation::class, 'city_corporation_id', 'id');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id', 'id');
    }
}
