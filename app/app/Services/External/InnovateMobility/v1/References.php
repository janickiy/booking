<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.04.2018
 * Time: 16:30
 */

namespace App\Services\External\InnovateMobility\v1;


use App\Services\External\InnovateMobility\Request;

/**
 * Class RailwayInfo
 * @package App\Services\External\InnovateMobility\v1
 * @method static getTransportNodes(array $options=[])
 * @method static getCountries(array $options=[])
 * @method static getCities(array $options=[])
 * @method static getRegions(array $options=[])
 */
class References extends Request
{
    protected static $basePath = 'Info/V1/References/';

    protected static $methods = [
        'TransportNodes',
        'Cities',
        'Countries',
        'Regions'
    ];

}