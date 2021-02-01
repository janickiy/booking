<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 06.04.2018
 * Time: 13:49
 */

namespace App\Models\References;


use App\Models\References\Traits\ByKeywords;
use App\Models\References\Traits\Cache;
use App\Models\References\Traits\NameByLocale;
use App\Models\Traits\RequestQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RailwayStation extends Model
{
    use Cache;
    use RequestQuery;
    use ByKeywords;
    use NameByLocale;

    protected $table = 'railway_station';
    protected $primaryKey = 'railwayStationId';

    protected $appends = ['name'];

    protected $fillable = [
        'countryId',
        'regionId',
        'cityId',
        'sourceId',
        'code',
        'nameRu',
        'nameEn',
        'isActive',
        'sourceUpdatedAt',
        'source',
        'info',
    ];

    protected $visible = [
        'sourceId',
        'regionId',
        'countryId',
        'cityId',
        'code',
        'nameRu',
        'nameEn',
        'info',
        'city',
        'country',
        'custom'
    ];

    protected static $attachable = [
        'country',
        'city'
    ];

    protected static $queryable = [
        'code',
    ];

    public function setInfoAttribute($value)
    {
        $this->attributes['info'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getInfoAttribute($value)
    {
        return json_decode($value);
    }

    public function setCustomAttribute($value)
    {
        $this->attributes['custom'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getCustomAttribute($value)
    {
        return json_decode($value);
    }

    public function city()
    {
        return $this->hasOne(City::class, 'sourceId', 'cityId');
    }

    public function country()
    {
        return $this->hasOne(Country::class, 'sourceId', 'countryId');
    }

    public function region()
    {
        return $this->hasOne(Region::class, 'sourceId', 'regionId');
    }

    public function scopeByName(Builder $query, $name)
    {
        return $query->where('nameRu', 'ilike', $name . '%')
            ->where('info->expressCode','!=', '')
            ->orWhere('nameRu', 'ilike', str_replace(' ', '-', $name) . '%')
            ->orWhere('nameRu', 'ilike', str_replace(' ', '%', $name) . '%')
            ->orWhere('nameRu', 'ilike', str_replace('.', '%', $name) . '%')
            ->orWhere('nameRu', 'ilike', str_replace('-', '%', $name) . '%')
            //->orWhere('nameRu', 'ilike', str_replace('-', '%-', $name) . '%')
            ->orderBy('info->popularity', 'desc');
    }


}