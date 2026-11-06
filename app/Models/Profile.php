<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    public const TYPE_PERSONAL = 'personal';
    public const TYPE_COMPANY = 'company';

    protected $fillable = [
        'profile_type', 'slug', 'status', 'is_verified', 'published_version_id',
        'created_by_admin_id', 'created_by_customer_id', 'published_at',
    ];

    protected function casts(): array
    {
        return ['is_verified' => 'boolean', 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string { return 'slug'; }

    public function versions(): HasMany { return $this->hasMany(ProfileVersion::class); }
    public function publishedVersion(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'published_version_id'); }
    public function latestVersion() { return $this->hasOne(ProfileVersion::class)->latestOfMany('version_number'); }
    public function createdByAdmin(): BelongsTo { return $this->belongsTo(User::class, 'created_by_admin_id'); }
    public function createdByCustomer(): BelongsTo { return $this->belongsTo(Customer::class, 'created_by_customer_id'); }

    public function customerAccess()
    {
        return $this->hasMany(ProfileCustomerAccess::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_version_id')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePersonal(Builder $query): Builder { return $query->where('profile_type', self::TYPE_PERSONAL); }
    public function scopeCompany(Builder $query): Builder { return $query->where('profile_type', self::TYPE_COMPANY); }

    public function publicUrl(): string
    {
        // The supplied design uses root-level profile URLs: /growbigdigital and /nazimuddinhridom.
        return url('/' . ltrim($this->slug, '/'));
    }
}
