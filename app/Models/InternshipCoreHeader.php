<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipCoreHeader extends Model
{
    protected $fillable = [
        'core_title', 'core_headline', 'core_paragraph',
    ];
}