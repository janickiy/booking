<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2018
 * Time: 14:51
 */

namespace App\Services\External\InnovateMobility\v1;

use App\Services\External\InnovateMobility\Request;

/**
 * Class BusSearch
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getRacePricing(array $options = [], boolean $map = false, array $mapOptions = []) Получение справки о вариантах проезда автобусом по указанному маршруту с информацией о ценах и наличии свободных мест
 * @method static getBusRoute(array $options = [], boolean $map = false, array $mapOptions = []) Получение информации о маршруте автобуса
 * @method static getRaceDetails(array $options = [], boolean $map = false, array $mapOptions = []) Получение информации необходимой для бронирования рейса
 */
class BusSearch extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Bus/V1/Search/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'RacePricing',
        // Получение справки о вариантах поездок на аэроэкспрессе
        'BusRoute',
        // Получение справки о варианте поездки на аэроэкспрессе
        'RaceDetails',
        // Получение справки о варианте поездки на аэроэкспрессе
    ];

    /**
     * {@inheritDoc}
     */
    protected static $lastError = [
        'Code' => 310,
        'Message' => 'Нет автобусов со свободными местами на эту дату'
    ];

    /**
     * {@inheritDoc}
     */
    protected static $cacheEnabled = [
        'TariffPricing' => 60 * 60 * 24,
    ];
}
