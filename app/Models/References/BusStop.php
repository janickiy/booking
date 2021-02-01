<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.04.2019
 * Time: 12:45
 */

namespace App\Models\References;


use App\Models\References\Traits\ByKeywords;
use App\Models\References\Traits\Cache;
use App\Models\References\Traits\NameByLocale;
use App\Models\Traits\RequestQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BusStop extends Model
{

    use Cache;
    use RequestQuery;
    use ByKeywords;
    use NameByLocale;

    protected $table = 'bus_stops';
    protected $primaryKey = 'busStopId';

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
        'isSuburban',
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
        'isSuburban',
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
}