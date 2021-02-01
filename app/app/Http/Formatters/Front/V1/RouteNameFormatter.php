<?php

namespace App\Http\Formatters\Front\V1;

use App\Http\Formatters\BaseFormatter;

class RouteNameFormatter extends BaseFormatter
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

        $data = [];
        foreach ($route as $key => $days) {
            $data[] = [
                'RouteName' => $days->RouteName,
                'Races' => $days->Races,
                'TariffId' => $days->TariffId,
            ];
        }
        return $data;
    }
}
