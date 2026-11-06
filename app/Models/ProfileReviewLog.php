<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileReviewLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['profile_version_id', 'action', 'admin_id', 'customer_id', 'note', 'created_at'];
    protected function casts(): array { return ['created_at' => 'datetime']; }
    public function version(): BelongsTo { return $this->belongsTo(ProfileVersion::class, 'profile_version_id'); }
    public function admin(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
