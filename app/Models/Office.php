<?php

namespace App\Models;

use App\Models\References\City;
use App\Models\Traits\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Class Office
 * @property int $id
 * @property string $contact_email
 * @property string $delivery_email
 * @property string $phone
 * @property boolean $closed
 * @property float $longitude
 * @property float $latitude
 * @property string $code
 * @property string $fax
 * @property string $schedule
 * @property string $sms_phone
 * @property string $iata_codes
 * @property $city
 * @property $address
 * @property integer $sort
 * @property integer $city_id
 * @property $name
 * @package App\Models
 * @method static Builder filter($data)
 */

class Office extends Model
{
    use Cache;
    public $timestamps = false;

    protected $fillable = [
        'contact_email',
        'delivery_email',
        'phone',
        'closed',
        'address',
        'longitude',
        'latitude',
        'code',
        'fax',
        'city',
        'sms_phone',
        'iata_codes',
        'sort',
        'name',
        'schedule',
        'city_id'
    ];

    protected $casts = [
        'name'      => 'array',
        'address'   => 'array',
        'city'      => 'array'
    ];

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopeFilter(Builder $query, array $data)
    {
        if (isset($data['id']))                                      $query->where('id', $data['id']);
        if (isset($data['ids']))                                     $query->whereIn('id', $data['ids']);
        if (isset($data['city_id']))                                 $query->where('city_id', $data['city_id']);
        if (isset($data['address']))                                  $query->where('address', 'ILIKE', '%' . $data['address'] . '%');
        return $query;
    }

    public function scopeClosest(Builder $query, float $lat, float $lon)
    {
        return $query->selectRaw('*, (POINT(?,?) <-> POINT(latitude,longitude)) AS distance',[$lat,$lon])
        ->orderBy('distance')
        ->limit(1)->first();
    }
}
