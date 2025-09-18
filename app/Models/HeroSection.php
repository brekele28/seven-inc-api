<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'heading',
        'subheading',
        'image_path',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}