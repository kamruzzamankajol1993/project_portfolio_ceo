<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileAccessRequest extends Model
{
    protected $fillable = ['customer_id', 'requested_profile_type', 'status', 'message', 'reviewed_by_admin_id', 'reviewed_at', 'review_note'];
    protected function casts(): array { return ['reviewed_at' => 'datetime']; }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function reviewedByAdmin(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_admin_id'); }
}
