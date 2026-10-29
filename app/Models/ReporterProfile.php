<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporterProfile extends Model
{
    protected $guarded = [];

    public function reporterTypes()
    {
        return $this->belongsToMany(ReporterType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function divisionData()
    {
        return $this->belongsTo(Division::class, 'division', 'id');
    }

    public function districtData()
    {
        return $this->belongsTo(District::class, 'district', 'id');
    }

    public function upazilaData()
    {
        return $this->belongsTo(Upazila::class, 'upazila', 'id');
    }

    public function unionData()
    {
        return $this->belongsTo(Union::class, 'union', 'id');
    }

    public function bloodGroupData()
    {
        return $this->belongsTo(BloodGroup::class, 'blood_group', 'id');
    }

    public function cityCorporationData()
    {
        return $this->belongsTo(CityCorporation::class, 'city_corporation', 'id');
    }

    public function wardData()
    {
        return $this->belongsTo(Ward::class, 'ward', 'id');
    }
}
