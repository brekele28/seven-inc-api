<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class News extends Model
{
    use SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'cover_path',
        'is_published', 'published_at', 'author',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected $appends = ['cover_url'];

    // ===== Accessors =====
    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? asset('storage/'.$this->cover_path) : null;
    }

    // ===== Scopes =====
    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    public function scopeOrdered($q)
    {
        // yang terbaru di-update duluan
        return $q->orderByDesc('updated_at')->orderByDesc('id');
    }

    // ===== Hooks =====
    protected static function boot()
    {
        parent::boot();

        static::creating(function (News $m) {
            if (empty($m->slug)) {
                $m->slug = static::makeUniqueSlug($m->title);
            }
            if ($m->is_published && empty($m->published_at)) {
                $m->published_at = now();
            }
        });

        static::updating(function (News $m) {
            if ($m->isDirty('title')) {
                $m->slug = static::makeUniqueSlug($m->title, $m->id);
            }
            if ($m->is_published && empty($m->published_at)) {
                $m->published_at = now();
            }
        });
    }

    public static function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (static::when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)->withTrashed()->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}