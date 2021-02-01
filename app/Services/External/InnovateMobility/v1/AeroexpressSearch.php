<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2018
 * Time: 14:51
 */

namespace App\Services\External\InnovateMobility\v1;

use App\Http\Formatters\Front\V1\TariffPriceInfoFormatter;
use App\Http\Formatters\Front\V1\TariffPricingFormatter;
use App\Services\External\InnovateMobility\Request;
use Illuminate\Support\Collection;

/**
 * Class RailwaySearch
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getTariffPricing(array $options = [], boolean $map = false, array $mapOptions = []) Получение справки о вариантах поездок на аэроэкспрессе
 * @method static getTariffPriceInfo(array $options = [], boolean $map = false, array $mapOptions = []) Получение справки о варианте поездки на аэроэкспрессе
 */
class AeroexpressSearch extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Aeroexpress/V1/Search/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'TariffPricing',
        // Получение справки о вариантах поездок на аэроэкспрессе
        'TariffPriceInfo',
        // Получение справки о варианте поездки на аэроэкспрессе
    ];

    /**
     * {@inheritDoc}
     */
    protected static $lastError = [
        'Code' => 310,
        'Message' => 'Нет поездов со свободными местами на эту дату'
    ];

    /**
     * {@inheritDoc}
     */
    protected static $cacheEnabled = [
        'TariffPricing'     => 60000 * 15,
        'TariffPriceInfo'   => 60000 * 15,
    ];

    protected static function mapTariffPricing($data, $options = [])
    {
        $tariffPricingFormatter = new TariffPricingFormatter();
        $aeroexpress = new Collection($data->Tariffs);
        $aeroexpress = $aeroexpress
            ->groupBy('TariffType')
            ->map(function (Collection $currentCars) use ($tariffPricingFormatter) {
                return $tariffPricingFormatter(new Collection($currentCars));
            });
        return $aeroexpress->values();
    }

    protected static function mapTariffPriceInfo($data, $options = [])
    {
            $tariffPricingFormatter = new TariffPriceInfoFormatter();
            $aeroexpress = $tariffPricingFormatter(new Collection($data));
            return $aeroexpress;
    }
}
