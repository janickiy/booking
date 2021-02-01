<?php

namespace App\Models\References;

use Illuminate\Database\Eloquent\Model;

class Trains extends Model
{
    protected $table = 'trains';
    protected $primaryKey = 'id';

    protected $fillable = [
        'trainNumber',
        'trainName',
        'trainDescription',
        'trainNumberToGetRoute',
        'carriers',
        'originStationCode',
        'destinationStationCode',
        'isAddedManually',
        'routeStops'
    ];

    /**
     * @param $value
     */
    public function setCarriersAttribute($value)
    {
        $this->attributes['carriers'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getCarriersAttribute($value)
    {
        return json_decode($value);
    }

    public function setRouteStopsAttribute($value)
    {
        $this->attributes['routeStops'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getRouteStopsAttribute($value)
    {
        return json_decode($value);
    }

    public function originStation()
    {
        return $this->hasOne(RailwayStation::class,'code', 'originStationCode');
    }

    public function destinationStation()
    {
        return $this->hasOne(RailwayStation::class,'code', 'destinationStationCode');
    }

}