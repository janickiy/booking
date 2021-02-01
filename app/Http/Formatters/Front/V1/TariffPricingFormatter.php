<?php

namespace App\Http\Formatters\Front\V1;

use App\Http\Formatters\BaseFormatter;

class TariffPricingFormatter extends BaseFormatter
{
    protected static $allowedRelations = [];

    protected $relations = [];

    /**
     * @param $aeroexpress
     * @return array|null
     */
    public function __invoke($aeroexpress): ?array
    {
        if (!$aeroexpress) {
            return null;
        }

        $routeNameFormatter = new RouteNameFormatter();

        $data = [
            'TariffId'      => $aeroexpress[0]->RouteName ? null : $aeroexpress[0]->TariffId,
            'Price'         => $aeroexpress[0]->Price,
            'TariffName'    => $aeroexpress[0]->TariffName,
            'TariffType'    => $aeroexpress[0]->TariffType,
            'Description'   => $aeroexpress[0]->Description,
            'RouteName'     => $aeroexpress[0]->RouteName ? $routeNameFormatter($aeroexpress) : null
        ];

        return $data;
    }
}
