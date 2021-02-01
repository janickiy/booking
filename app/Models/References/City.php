<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 06.04.2018
 * Time: 13:48
 */

namespace App\Models\References;


use App\Models\References\Traits\ByKeywords;
use App\Models\References\Traits\Cache;
use App\Models\References\Traits\NameByLocale;
use App\Models\Traits\RequestQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    use Cache;
    use RequestQuery;
    use ByKeywords;
    use NameByLocale;

    protected $table = 'city';
    protected $primaryKey = 'cityId';

    protected $fillable = [
        'countryId',
        'regionId',
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
        'code',
        'nameRu',
        'nameEn',
        'info',
        'country',
        'stations',
        'airports',
        'busStops'
    ];

    protected static $queryable = [
        'regionId',
        'info->expressCode',
    ];

    protected static $attachable = [
        'country',
        'stations',
        'airports',
        'busStops',
    ];


    public function setInfoAttribute($value)
    {
        $this->attributes['info'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getInfoAttribute($value)
    {
        return json_decode($value);
    }

    public function country()
    {
        return $this->hasOne(Country::class,'sourceId', 'countryId');
    }

    public function stations()
    {
        return $this->hasMany(RailwayStation::class, 'cityId', 'sourceId');
    }

    public function airports()
    {
        return $this->hasMany(Airport::class,'cityId', 'sourceId');
    }

    public function busStops()
    {
        return $this->hasMany(BusStop::class,'cityId', 'sourceId');
    }

}