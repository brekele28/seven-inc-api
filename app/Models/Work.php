<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use SoftDeletes;

    protected $table = 'works';

    protected $fillable = [
        'heading', 'title', 'subtitle', 'hero_path',
    ];

    protected $appends = ['hero_url'];

    public function getHeroUrlAttribute(): ?string
    {
        return $this->hero_path ? asset('storage/'.$this->hero_path) : null;
    }
}