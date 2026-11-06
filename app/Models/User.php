<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'phone', 'email', 'image', 'password', 'status', 'address',
        'bio', 'email_verified_at',
    ];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    /** Admin-created public profiles. Client accounts live in the separate customers table. */
    public function createdProfiles(): HasMany
    {
        return $this->hasMany(Profile::class, 'created_by_admin_id');
    }

    public function reviewedProfileVersions(): HasMany
    {
        return $this->hasMany(ProfileVersion::class, 'reviewed_by_admin_id');
    }

    public function reviewedProfileAccessRequests(): HasMany
    {
        return $this->hasMany(ProfileAccessRequest::class, 'reviewed_by_admin_id');
    }

    public function isFullSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function getImageUrlAttribute(): ?string
    {
        // Requested deployment layout: /public/uploads/... from the site root.
        return $this->image ? asset('public/' . ltrim($this->image, '/')) : null;
    }
}
