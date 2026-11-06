<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'id', 'site_name','tagline','site_url','contact_email','contact_phone','address',
        'logo','favicon','footer_text','facebook_url','instagram_url','youtube_url',
        'linkedin_url','maintenance_mode','seo_title','seo_description',
        'seo_keywords','seo_robots','seo_canonical','seo_og_image','seo_og_type',
        'seo_twitter_card','google_analytics_id','google_site_verification',
        'head_scripts','body_scripts',
    ];
    protected function casts(): array { return ['maintenance_mode'=>'boolean']; }
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id'=>1], ['site_name'=>'EntrepreneurWiki']);
    }
    public function publicUrl(string $column): ?string
    {
        return $this->{$column} ? asset('public/'.ltrim($this->{$column}, '/')) : null;
    }
}
