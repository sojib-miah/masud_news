<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table = 'divisions';

    protected $guarded = [];
    public $timestamps = false;
    public $incrementing = false;   // IDs are 1..8, not auto-increment
    protected $keyType = 'int';

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
