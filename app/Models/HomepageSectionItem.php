<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSectionItem extends Model
{
    protected $fillable = [
        'homepage_section_id', 'profile_id', 'post_id', 'item_type', 'title_override', 'subtitle',
        'description', 'value_text', 'value_source', 'icon', 'image_path', 'url', 'metadata',
        'is_enabled', 'sort_order',
    ];
    protected function casts(): array { return ['metadata' => 'array', 'is_enabled' => 'boolean']; }
    public function section(): BelongsTo { return $this->belongsTo(HomepageSection::class, 'homepage_section_id'); }
    public function profile(): BelongsTo { return $this->belongsTo(Profile::class); }
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
}
