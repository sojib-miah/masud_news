<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PourashavaWard extends Model
{
    protected $table = 'pourashava_wards';

    protected $guarded = [];

    public function pourashava()
    {
        return $this->belongsTo(Pourashava::class, 'pourashava_id');
    }
}
