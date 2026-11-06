<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileSection extends Model
{
    protected $fillable = ['profile_version_id', 'section_key', 'title', 'intro', 'section_type', 'layout', 'settings', 'is_visible', 'sort_order'];
    protected function casts(): array { return ['settings' => 'array', 'is_visible' => 'boolean']; }
    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
    public function items(): HasMany { return $this->hasMany(ProfileSectionItem::class, 'section_id')->orderBy('sort_order'); }
}
