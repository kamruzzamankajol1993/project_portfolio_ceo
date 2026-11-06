<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileCustomerAccess extends Model
{
    protected $table = 'profile_customer_access';
    protected $fillable = ['profile_id', 'customer_id', 'access_level', 'is_primary', 'granted_by_admin_id'];
    protected function casts(): array { return ['is_primary' => 'boolean']; }
    public function profile(): BelongsTo { return $this->belongsTo(Profile::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function grantedByAdmin(): BelongsTo { return $this->belongsTo(User::class, 'granted_by_admin_id'); }
}
