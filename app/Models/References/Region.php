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

class Region extends Model
{

    use Cache;
    use RequestQuery;
    use ByKeywords;
    use NameByLocale;

    protected $table = 'region';
    protected $primaryKey = 'regionId';

    protected $fillable = [
        'countryId',
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
        'countryId',
        'sourceId',
        'code',
        'nameRu',
        'nameEn',
        'cities',
        'country'
    ];

    protected static $attachable = [
        'cities',
        'country'
    ];

    protected static $queryable = [
        'countryId',
        'code',
        'sourceId'
    ];


    public function cities()
    {
        return $this->hasMany(City::class, 'regionId', 'sourceId');
    }

    public function country()
    {
        return $this->hasOne(Country::class,'sourceId', 'countryId');
    }
}