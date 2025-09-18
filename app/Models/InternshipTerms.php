<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipTerms extends Model
{
    protected $table = 'internship_terms';

    protected $fillable = [
        'subtitle',
        'headline',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}