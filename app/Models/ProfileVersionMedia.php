<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVersionMedia extends Model
{
    protected $table = 'profile_version_media';
    protected $fillable = ['profile_version_id', 'media_asset_id', 'role', 'sort_order'];
    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
    public function media(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'media_asset_id'); }
}
