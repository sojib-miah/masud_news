<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Union extends Model
{
    protected $table = 'unions';

    protected $guarded = [];
    public $timestamps = false;
    public $incrementing = false;   // IDs are 1..8, not auto-increment
    protected $keyType = 'int';

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }
}
