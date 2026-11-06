<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use App\Notifications\CustomerPasswordResetNotification;
use App\Notifications\CustomerVerifyEmailNotification;

class Customer extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $guard = 'customer';

    protected $fillable = [
        // Privileged fields (customer_code, status, email_verified_at, last_login_at)
        // are intentionally not mass assignable. Set them in trusted services/controllers.
        'name', 'email', 'phone', 'password', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $customer): void {
            if (blank($customer->customer_code)) {
                $customer->customer_code = 'CUS-' . Str::upper((string) Str::ulid());
            }
        });
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(ProfileAccessRequest::class);
    }

    public function profileTypeAccess(): HasMany
    {
        return $this->hasMany(CustomerProfileAccess::class);
    }

    public function managedProfiles()
    {
        return $this->belongsToMany(Profile::class, 'profile_customer_access')
            ->withPivot(['access_level', 'is_primary', 'granted_by_admin_id'])
            ->withTimestamps();
    }

    public function createdProfiles(): HasMany
    {
        return $this->hasMany(Profile::class, 'created_by_customer_id');
    }

    public function createdProfileVersions(): HasMany
    {
        return $this->hasMany(ProfileVersion::class, 'created_by_customer_id');
    }

    public function reviewLogEntries(): HasMany
    {
        return $this->hasMany(ProfileReviewLog::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('public/' . ltrim($this->avatar, '/')) : null;
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill(['email_verified_at' => $this->freshTimestamp()])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'customer.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        $this->notify(new CustomerVerifyEmailNotification($verificationUrl));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomerPasswordResetNotification($token));
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->email_verified_at !== null;
    }
}
