<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_work_id',
        'intro_text',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(RequirementItem::class)->orderBy('sort_order');
    }

    // Helper supaya mudah ambil per kategori
    public function itemsByType(string $type)
    {
        return $this->items()->where('type', $type);
    }
}