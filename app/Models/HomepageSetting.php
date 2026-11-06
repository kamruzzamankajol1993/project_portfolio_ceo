<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $fillable = ['setting_key', 'group_key', 'label', 'field_type', 'value', 'sort_order'];
    protected function casts(): array { return ['value' => 'array']; }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('setting_key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
