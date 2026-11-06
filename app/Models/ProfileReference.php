<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileReference extends Model
{
    protected $fillable = ['profile_version_id', 'title', 'publisher', 'url', 'source_date', 'description', 'sort_order'];
    protected function casts(): array { return ['source_date' => 'date']; }
    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
}
