<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalProfileDetail extends Model
{
    protected $primaryKey = 'profile_version_id';
    public $incrementing = false;

    protected $fillable = [
        'profile_version_id', 'full_name', 'headline', 'professional_title', 'current_position',
        'company_organisation', 'industry_id', 'location', 'hometown', 'nationality', 'date_of_birth',
        'occupation', 'public_contact_email', 'public_phone', 'personal_website', 'short_bio', 'about',
        'profile_quote', 'early_life', 'career_entrepreneurship', 'education_summary',
        'awards_publications_recognition', 'show_public_email', 'show_public_phone', 'show_date_of_birth',
        'career_timeline', 'affiliations', 'education_items', 'achievement_items', 'media_items',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'show_public_email' => 'boolean',
            'show_public_phone' => 'boolean',
            'show_date_of_birth' => 'boolean',
            'career_timeline' => 'array',
            'affiliations' => 'array',
            'education_items' => 'array',
            'achievement_items' => 'array',
            'media_items' => 'array',
        ];
    }

    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
    public function industry(): BelongsTo { return $this->belongsTo(Industry::class); }
}
