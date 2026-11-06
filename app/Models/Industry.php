<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Industry extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function personalProfileDetails(): HasMany
    {
        return $this->hasMany(PersonalProfileDetail::class);
    }

    public function companyProfileDetails(): HasMany
    {
        return $this->hasMany(CompanyProfileDetail::class);
    }
}
