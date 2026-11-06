<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageSection extends Model
{
    protected $fillable = ['section_key', 'section_type', 'title', 'subtitle', 'content', 'settings', 'is_enabled', 'sort_order'];
    protected function casts(): array { return ['settings' => 'array', 'is_enabled' => 'boolean']; }
    public function items(): HasMany { return $this->hasMany(HomepageSectionItem::class)->orderBy('sort_order'); }
    public function scopeEnabled(Builder $query): Builder { return $query->where('is_enabled', true)->orderBy('sort_order'); }
}
