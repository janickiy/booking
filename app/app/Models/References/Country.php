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
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use Cache;
    use RequestQuery;
    use ByKeywords;
    use NameByLocale;

    protected $table = 'country';
    protected $primaryKey = 'countryId';

    protected $fillable = [
        'sourceId',
        'code',
        'nameRu',
        'nameEn',
        'isActive',
        'source',
        'info',
        'sourceUpdatedAt',
    ];

    protected $visible = [
        'sourceId',
        'code',
        'nameRu',
        'nameEn',
        'regions'
    ];

    protected static $attachable = [
        'regions'
    ];

    protected static $queryable = [
       ''
    ];

    public function regions()
    {
        return $this->hasMany(Region::class,'countryId', 'sourceId');
    }
}