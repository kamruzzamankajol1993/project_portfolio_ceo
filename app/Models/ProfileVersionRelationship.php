<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVersionRelationship extends Model
{
    protected $fillable = ['profile_version_id', 'related_profile_id', 'relation_type', 'position', 'description', 'is_visible', 'sort_order'];
    protected function casts(): array { return ['is_visible' => 'boolean']; }
    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
    public function relatedProfile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'related_profile_id')
            ->where('profiles.status', 'published')
            ->whereNotNull('profiles.published_version_id')
            ->whereNotNull('profiles.published_at')
            ->where('profiles.published_at', '<=', now());
    }
}
