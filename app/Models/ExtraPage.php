<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraPage extends Model
{
    protected $fillable = [
        'page_key', 'title', 'slug', 'content', 'meta_title', 'meta_description',
        'meta_keywords', 'meta_robots', 'canonical_url', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
