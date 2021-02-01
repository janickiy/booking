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

class Airport extends Model
{
    use Cache;
    use RequestQuery;
    use ByKeywords;
    use NameByLocale;

    protected $table='airport';
    protected $primaryKey='airportId';

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
        'country'
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
        return $this->hasOne(City::class,'sourceId', 'cityId');
    }

    public function country()
    {
        return $this->hasOne(Country::class,'sourceId', 'countryId');
    }

}