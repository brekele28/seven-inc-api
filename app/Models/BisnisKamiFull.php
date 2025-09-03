<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BisnisKamiFull extends Model
{
    protected $table = 'bisnis_kami_full';

    protected $fillable = [
        'header_image','header_subtitle','header_title','general_description',
        'seven_tech_title','seven_tech_text','seven_tech_image',
        'seven_style_title','seven_style_text','seven_style_image',
        'seven_serve_title','seven_serve_text','seven_serve_image',
        'seven_edu_title','seven_edu_text','seven_edu_image',
    ];
}