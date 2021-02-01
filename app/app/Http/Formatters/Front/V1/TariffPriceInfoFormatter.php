<?php

namespace App\Http\Formatters\Front\V1;

use App\Http\Formatters\BaseFormatter;
use Illuminate\Support\Collection;

class TariffPriceInfoFormatter extends BaseFormatter
{
    protected static $allowedRelations = [];

    protected $relations = [];

    /**
     * @param Collection $aeroexpress
     * @return array|null
     */
    public function __invoke($aeroexpress): ?array
    {
        if (!$aeroexpress) {
            return null;
        }
        $routeNameInfoFormatter = new RouteNameInfoFormatter();

        $data = [
            'TariffId'      => $aeroexpress['RouteName'] ? null : $aeroexpress['TariffId'],
            'Price'         => $aeroexpress['Price'],
            'TariffName'    => $aeroexpress['TariffName'],
            'TariffType'    => $aeroexpress['TariffType'],
            'Description'   => $aeroexpress['Description'],
            'DocumentTypes' => $aeroexpress['DocumentTypes'],
            'RouteName'     => $aeroexpress['RouteName'] ? $routeNameInfoFormatter($aeroexpress) : null
        ];

        return $data;
    }
}
