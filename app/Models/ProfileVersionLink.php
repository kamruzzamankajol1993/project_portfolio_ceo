<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVersionLink extends Model
{
    protected $fillable = ['profile_version_id', 'link_type', 'label', 'url', 'icon', 'is_visible', 'sort_order'];
    protected function casts(): array { return ['is_visible' => 'boolean']; }
    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
}
