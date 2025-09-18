<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipCoreCard extends Model
{
    protected $fillable = [
        'title', 'description', 'image_path', 'style_type', 'order',
    ];
}