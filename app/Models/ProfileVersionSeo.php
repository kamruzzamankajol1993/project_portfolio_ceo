<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVersionSeo extends Model
{
    protected $table = 'profile_version_seo';
    protected $fillable = [
        'profile_version_id', 'meta_title', 'meta_description', 'meta_keywords', 'robots',
        'canonical_url', 'og_title', 'og_description', 'og_image', 'twitter_title',
        'twitter_description', 'twitter_image',
    ];
    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
}
