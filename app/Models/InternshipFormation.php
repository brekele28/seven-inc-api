<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipFormation extends Model
{
    protected $fillable = ['subtitle','headline','paragraph'];

    public function cards() {
        return $this->hasMany(InternshipFormationCard::class, 'formation_id')->orderBy('order');
    }
}