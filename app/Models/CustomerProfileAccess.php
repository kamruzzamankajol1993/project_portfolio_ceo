<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfileAccess extends Model
{
    protected $table = 'customer_profile_access';
    protected $fillable = ['customer_id', 'profile_type', 'can_create', 'granted_by_admin_id', 'granted_at', 'revoked_at'];
    protected function casts(): array { return ['can_create' => 'boolean', 'granted_at' => 'datetime', 'revoked_at' => 'datetime']; }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function grantedByAdmin(): BelongsTo { return $this->belongsTo(User::class, 'granted_by_admin_id'); }
    public function scopeActive($query) { return $query->where('can_create', true)->whereNull('revoked_at'); }
}
