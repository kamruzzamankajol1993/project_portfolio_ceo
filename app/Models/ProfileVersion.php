<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProfileVersion extends Model
{
    public const DRAFT = 'draft';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'profile_id', 'version_number', 'proposed_slug', 'review_status', 'created_by_admin_id',
        'created_by_customer_id', 'submitted_by_customer_id', 'reviewed_by_admin_id',
        'submitted_at', 'reviewed_at', 'review_note', 'change_summary',
    ];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime'];
    }

    public function profile(): BelongsTo { return $this->belongsTo(Profile::class); }
    public function createdByAdmin(): BelongsTo { return $this->belongsTo(User::class, 'created_by_admin_id'); }
    public function createdByCustomer(): BelongsTo { return $this->belongsTo(Customer::class, 'created_by_customer_id'); }
    public function submittedByCustomer(): BelongsTo { return $this->belongsTo(Customer::class, 'submitted_by_customer_id'); }
    public function reviewedByAdmin(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_admin_id'); }
    public function personalDetails(): HasOne { return $this->hasOne(PersonalProfileDetail::class); }
    public function companyDetails(): HasOne { return $this->hasOne(CompanyProfileDetail::class); }
    public function sections(): HasMany { return $this->hasMany(ProfileSection::class)->orderBy('sort_order'); }
    public function links(): HasMany { return $this->hasMany(ProfileVersionLink::class)->orderBy('sort_order'); }
    public function references(): HasMany { return $this->hasMany(ProfileReference::class)->orderBy('sort_order'); }
    public function relationships(): HasMany { return $this->hasMany(ProfileVersionRelationship::class)->orderBy('sort_order'); }
    public function mediaLinks(): HasMany { return $this->hasMany(ProfileVersionMedia::class)->orderBy('sort_order'); }
    public function seo(): HasOne { return $this->hasOne(ProfileVersionSeo::class); }
    public function reviewLogs(): HasMany { return $this->hasMany(ProfileReviewLog::class)->orderByDesc('created_at'); }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('review_status', self::PENDING);
    }
}
