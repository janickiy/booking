<?php

namespace App\Http\Formatters\Front\V1;

use App\Http\Formatters\BaseFormatter;
use Illuminate\Support\Collection;

class RouteNameInfoFormatter extends BaseFormatter
{
    protected static $allowedRelations = [];

    protected $relations = [];

    /**
     * @param $aeroexpress
     * @return array|null
     */
    public function __invoke($route): ?array
    {
        if (!$route) {
            return null;
        }
        $data[] = [
            'RouteName' => $route['RouteName'],
            'Races' => (new Collection($route['Races']))->keyBy('RaceId'),
            'TariffId' => $route['TariffId'],
        ];
        return $data;
    }
}
