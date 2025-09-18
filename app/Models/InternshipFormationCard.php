<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipFormationCard extends Model
{
    protected $fillable = ['formation_id','title','image_path','order'];

    protected $appends = ['image_url'];

    public function formation() {
        return $this->belongsTo(InternshipFormation::class, 'formation_id');
    }

    public function getImageUrlAttribute() {
        return $this->image_path ? asset('storage/'.$this->image_path) : null;
    }
}