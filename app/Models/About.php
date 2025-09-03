<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    protected $fillable = [
        'subtitle',
        'headline',
        'hero_image1',
        'hero_image2',
        'hero_image3',
        'left_p1',
        'left_p2',
        'left_p3',
        'right_p1',
        'right_p2',
        'core_title',
        'core_headline',
        'core_paragraph',
    ];

    public $timestamps = true;
}