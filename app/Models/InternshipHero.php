<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipHero extends Model
{
    protected $fillable = [
        'subtitle',
        'title',
        'image_path',
    ];
}