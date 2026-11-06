<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAsset extends Model
{
    protected $fillable = [
        'uploaded_by_admin_id', 'uploaded_by_customer_id', 'collection', 'disk', 'path',
        'original_name', 'mime_type', 'size_bytes', 'width', 'height', 'alt_text', 'caption',
    ];
    protected function casts(): array { return ['size_bytes' => 'integer', 'width' => 'integer', 'height' => 'integer']; }
    public function uploadedByAdmin(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by_admin_id'); }
    public function uploadedByCustomer(): BelongsTo { return $this->belongsTo(Customer::class, 'uploaded_by_customer_id'); }
    public function profileVersions(): HasMany { return $this->hasMany(ProfileVersionMedia::class); }
    public function getUrlAttribute(): string { return asset('public/' . ltrim($this->path, '/')); }
}
