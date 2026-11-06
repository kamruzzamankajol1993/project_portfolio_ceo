<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyProfileDetail extends Model
{
    protected $primaryKey = 'profile_version_id';
    public $incrementing = false;

    protected $fillable = [
        'profile_version_id', 'company_name', 'tagline', 'industry_id', 'founded_year', 'headquarters',
        'company_size', 'business_type', 'area_served', 'official_website', 'public_email', 'public_phone',
        'short_description', 'overview', 'history_background', 'mission', 'vision', 'parent_company',
        'show_public_email', 'show_public_phone', 'accuracy_cta_enabled', 'accuracy_cta_title',
        'accuracy_cta_description', 'accuracy_cta_button_label', 'accuracy_cta_button_url',
        'accuracy_cta_secondary_label', 'accuracy_cta_secondary_url',
    ];

    protected function casts(): array
    {
        return ['founded_year' => 'integer', 'show_public_email' => 'boolean', 'show_public_phone' => 'boolean', 'accuracy_cta_enabled' => 'boolean'];
    }

    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
    public function industry(): BelongsTo { return $this->belongsTo(Industry::class); }
}
