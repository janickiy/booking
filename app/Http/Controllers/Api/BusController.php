<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2018
 * Time: 16:15
 */

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\V1\Bus\BusRouteRequest;
use App\Http\Requests\Api\V1\Bus\RaceDetailsRequest;
use App\Http\Requests\Api\V1\Bus\RacePricingRequest;
use App\Services\External\InnovateMobility\v1\BusSearch;

/**
 * Class BusController
 * [Методы для получения информации о Автобусах]
 * @group BusController info
 * @package App\Http\Controllers\Api
 */
class BusController extends BaseController
{
    /**
     * RacePricing
     * [Получение справки о вариантах проезда автобусом по указанному маршруту
     * с информацией о ценах и наличии свободных мест]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Bus-V1-Search-RacePricing
     * @bodyParam OriginCode required string.
     * @bodyParam DestinationCode required string.
     * @bodyParam DepartureDate required date.
     * @param RacePricingRequest $request
     * @return void
     */
    public function racePricing(RacePricingRequest $request)
    {
        $options = [
            "OriginCode"        => $request->input('OriginCode'),
            "DestinationCode"   => $request->input('DestinationCode'),
            "DepartureDate"     => $request->input('DepartureDate'),
        ];
        $result = BusSearch::getRacePricing($options);
        dd($result);
    }

    /**
     * BusRoute
     * [Получение информации о маршруте автобуса]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Bus-V1-Search-BusRoute
     * @bodyParam RaceId required string.
     * @bodyParam Provider required string.
     * @bodyParam DepartureDate required date.
     * @param BusRouteRequest $request
     * @return void
     */
    public function busRoute(BusRouteRequest $request)
    {
        $options = [
            "Provider"      => $request->input('Provider'),
            "DepartureDate" => $request->input('DepartureDate'),
            "RaceId"        => $request->input('RaceId'),
        ];
        $result = BusSearch::getBusRoute($options);
        dd($result);
    }

    /**
     * raceDetails
     * [Получение информации необходимой для бронирования рейса]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Bus-V1-Search-RaceDetails
     * @bodyParam RaceId required string.
     * @bodyParam Provider required string.
     * @bodyParam DepartureDate required date.
     * @param RaceDetailsRequest $request
     * @return void
     */
    public function raceDetails(RaceDetailsRequest $request)
    {
        $options = [
            "Provider"      => $request->input('Provider'),
            "DepartureDate" => $request->input('DepartureDate'),
            "RaceId"        => $request->input('RaceId'),
        ];
        $result = BusSearch::getRaceDetails($options);
        dd($result);
    }
}
