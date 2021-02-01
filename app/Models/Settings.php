<?php

namespace App\Models;

use App\Models\References\Traits\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Settings extends Model
{
    use Cache;

    protected $primaryKey = 'settingId';

    protected $fillable = [
        'name',
        'description',
        'value',
        'accessLevel'
    ];

    public function scopeByLevel(Builder $query, $accessLevel)
    {
        return $query->where('accessLevel','<=', $accessLevel);
    }

    public function setNameAttribute($value) {
        $this->attributes['name'] = str_replace(' ', '_', $value);
    }
}
