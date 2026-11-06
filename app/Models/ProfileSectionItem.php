<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileSectionItem extends Model
{
    protected $fillable = ['section_id', 'title', 'subtitle', 'description', 'icon', 'image_path', 'url', 'metadata', 'is_visible', 'sort_order'];
    protected function casts(): array { return ['metadata' => 'array', 'is_visible' => 'boolean']; }
    public function section(): BelongsTo { return $this->belongsTo(ProfileSection::class, 'section_id'); }
}
