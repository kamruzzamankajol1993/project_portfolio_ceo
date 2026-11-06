<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteNavigationMenu extends Model
{
    protected $fillable = ['menu_key', 'title', 'is_enabled', 'sort_order'];
    protected function casts(): array { return ['is_enabled' => 'boolean']; }
    public function items(): HasMany { return $this->hasMany(SiteNavigationItem::class, 'menu_id')->orderBy('sort_order'); }
    public function scopeEnabled(Builder $query): Builder { return $query->where('is_enabled', true)->orderBy('sort_order'); }
}
