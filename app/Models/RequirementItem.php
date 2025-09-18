<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequirementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requirement_id',
        'type',
        'text',
        'sort_order',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }
}