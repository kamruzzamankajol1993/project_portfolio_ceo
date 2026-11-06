<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteNavigationItem extends Model
{
    protected $fillable = ['menu_id', 'parent_id', 'label', 'url', 'route_name', 'target', 'icon', 'css_class', 'is_enabled', 'sort_order'];
    protected function casts(): array { return ['is_enabled' => 'boolean']; }
    public function menu(): BelongsTo { return $this->belongsTo(SiteNavigationMenu::class, 'menu_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
}
