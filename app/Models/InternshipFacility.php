<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipFacility extends Model
{
    protected $fillable = ['subtitle','headline'];

    public function items() {
        return $this->hasMany(InternshipFacilityItem::class, 'facility_id')->orderBy('order');
    }
}