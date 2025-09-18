<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipFacilityItem extends Model
{
    protected $fillable = ['facility_id','text','order'];

    public function facility() {
        return $this->belongsTo(InternshipFacility::class, 'facility_id');
    }
}