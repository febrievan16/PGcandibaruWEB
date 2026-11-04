<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug',
    'title',
    'category',
    'author_name',
    'excerpt',
    'content',
    'cover_image',
    'reading_time_minutes',
    'is_published',
    'published_at',
])]
class Article extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reading_time_minutes' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}

