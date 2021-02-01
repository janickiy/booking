<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2018
 * Time: 14:51
 */

namespace App\Services\External\InnovateMobility\v1;

use App\Helpers\StringHelpers;
use App\Http\Formatters\Front\V1\CarPricingFormatter;
use App\Models\References\City;
use App\Models\References\RailwayStation;
use App\Models\References\Trains;
use App\Services\External\InnovateMobility\{Request};
use Illuminate\Support\Collection;
use App\Services\Settings;
use App\Helpers\LangHelper;

/**
 * Class RailwaySearch
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getTrainPricing(array $options = [], boolean $map = false, array $mapOptions = []) Получение поездов с информацией о маршруте, ценах и тп. по заданным параметрам
 * @method static getCarPricing(array $options = [], boolean $map = false, array $mapOptions = []) Получение вагонов по заданному поезду
 * @method static getSchedule(array $options = [], boolean $map, array $mapOptions = []) Получение расписания по выбранному направлению
 * @method static getTrainRoute(array $options = [], boolean $map, array $mapOptions = []) Получение маршрута следования поезда
 * @method static getRoutes(array $options = [], boolean $map, array $mapOptions = []) Поиск маршрутов с одной пересадкой
 * @method static getRoutePricing(array $options = [], boolean $map, array $mapOptions = []) Поиск маршрутов с одной пересадкой с проверкой цены и наличия мест. Только для направлений, связанных с Крымом!
 * @method static getMeal(array $options = [], boolean $map, array $mapOptions = []) Получение вариантов питания
 */
class RailwaySearch extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Railway/V1/Search/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'TrainPricing',
        // Получение поездов с информацией о маршруте, ценах и тп. по заданным параметрам
        'CarPricing',
        // Получение вагонов по заданному поезду
        'Schedule',
        // Получение расписания по выбранному направлению
        'TrainRoute',
        // Получение маршрута следования поезда
        'Routes',
        // Поиск маршрутов с одной пересадкой
        'RoutePricing',
        // Поиск маршрутов с одной пересадкой с проверкой цены и наличия мест. Только для направлений, связанных с Крымом!
        'Meal',
        // Получение вариантов питания
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
        'TrainPricing' => 60 * 60 * 150,
        'TrainRoute' => 60 * 60 * 150,
        'CarPricing' => 60 * 60 * 10,
        'Schedule' => 60 * 60 * 150,
    ];

    /**
     * Маппинг метод результата TrainPricing
     * @param array $data
     * @param array $options
     * @return array
     */
    protected static function mapTrainPricing($data, $options = [])
    {

        $allowZeroPlace = false;
        if (isset($options['allowZeroPlace'])) {
            $allowZeroPlace = (boolean)(int)$options['allowZeroPlace'];
        }

        $settings = new Settings();

        $totalTax = (float)$settings->get('taxRailwayImRzdPurchase') + (float)$settings->get('taxRailwayImTrivagoPurchase');

        $compactList = [];
        foreach ($data->Trains as $train) {
            $carsList = [];

            $carsPlacesSummary = [
                'lowerQnt' => 0,
                'upperQnt' => 0,
                'lowerSideQnt' => 0,
                'upperSideQnt' => 0,
                'maleQnt' => 0,
                'femaleQnt' => 0,
                'mixedQnt' => 0,
            ];

            foreach ($train->CarGroups as $carGroup) {
                if (($carGroup->TotalPlaceQuantity > 0 || $allowZeroPlace) && !$carGroup->IsSaleForbidden && $carGroup->AvailabilityIndication == 'Available') {

                    $carsList[] = [
                        'typeRu' => LangHelper::trans('references/im.carTypes.' . $carGroup->CarType),
                        'typeEn' => $carGroup->CarType,

                        'typeScheme' => LangHelper::trans('references/im.schemeCarTypes.' . $carGroup->CarType),
                        'type' => LangHelper::trans('references/im.carTypes.' . $carGroup->CarType),

                        'minPrice' => (float)$carGroup->MinPrice + $totalTax,
                        'maxPrice' => (float)$carGroup->MaxPrice + $totalTax,
                        'tax' => [
                            'rzhd' => (float)$settings->get('taxRailwayImRzdPurchase'),
                            'agent' => (float)$settings->get('taxRailwayImTrivagoPurchase'),
                            'total' => $totalTax,
                        ],
                        'qnt' => $carGroup->TotalPlaceQuantity,
                        'lowerQnt' => $carGroup->LowerPlaceQuantity,
                        'upperQnt' => $carGroup->UpperPlaceQuantity,
                        'lowerSideQnt' => $carGroup->LowerSidePlaceQuantity,
                        'upperSideQnt' => $carGroup->UpperSidePlaceQuantity,
                        'maleQnt' => $carGroup->MalePlaceQuantity,
                        'femaleQnt' => $carGroup->FemalePlaceQuantity,
                    ];

                    $carsPlacesSummary['lowerQnt'] += $carGroup->LowerPlaceQuantity;
                    $carsPlacesSummary['upperQnt'] += $carGroup->UpperPlaceQuantity;
                    $carsPlacesSummary['lowerSideQnt'] += $carGroup->LowerSidePlaceQuantity;
                    $carsPlacesSummary['upperSideQnt'] += $carGroup->UpperSidePlaceQuantity;
                    $carsPlacesSummary['maleQnt'] += $carGroup->MalePlaceQuantity;
                    $carsPlacesSummary['femaleQnt'] += $carGroup->FemalePlaceQuantity;
                    $carsPlacesSummary['mixedQnt'] += $carGroup->MixedCabinQuantity;
                }
            }
            $services = [];

            foreach ($train->CarServices as $service) {
                // $services[] = self::REFERENCES['carService'][$service];
                $services[] = LangHelper::trans('references/im.carService.' . $service);
            }

            $originStation = RailwayStation::where('code', $train->OriginStationCode)->with([
                'city',
                'region'
            ])->first();
            $arrivalStation = RailwayStation::where('code', $train->DestinationStationCode)->with([
                'city',
                'region'
            ])->first();

            $originStationCity = $originStation->city;
            $arrivalStationCity = $arrivalStation->city;

            if (!$originStationCity) {
                $originStationCity = City::where('info->expressCode', $train->OriginStationCode)->first();
            }
            if (!$originStationCity) {
                $originStationCity = $originStation->region;
            }

            if (!$arrivalStationCity) {
                $arrivalStationCity = City::where('info->expressCode', $train->DestinationStationCode)->first();
            }
            if (!$arrivalStationCity) {
                $arrivalStationCity = $arrivalStation->region;
            }

            $originNameArr = explode(' ', $train->OriginName);
            $destinationNameArr = explode(' ', $train->DestinationName);

            $routeTrain = Trains::where('trainNumber', $train->TrainNumberToGetRoute)->first();

            $routeOriginStation = false;
            $routeDestinationStation = false;

            if ($routeTrain && $routeTrain->originStationCode !== '') {
                $routeOriginStation = $routeTrain->originStation()->with('city')->first();
                $routeDestinationStation = $routeTrain->destinationStation()->with('city')->first();
            }

            if (!$routeOriginStation) {
                $routeOriginStation = RailwayStation::byName($train->OriginName)->with('city')->first();
            }
            if (!$routeOriginStation) {
                $routeOriginStation = RailwayStation::byName($train->OriginName)->orWhere('nameRu', 'ilike',
                    $originNameArr[0] . '%')->with('city')->first();
            }

            if (!$routeOriginStation) {
                $routeDestinationStation = RailwayStation::byName($train->DestinationName)->with('city')->first();
            }
            if (!$routeDestinationStation) {
                $routeDestinationStation = RailwayStation::byName($train->DestinationName)->orWhere('nameRu', 'ilike',
                    $destinationNameArr[0] . '%')->with('city')->first();
            }


            $routeOriginNameRu = $routeOriginStation ? ($routeOriginStation->city ? $routeOriginStation->city->nameRu : mb_convert_case($routeOriginStation->nameRu,
                MB_CASE_TITLE)) : mb_convert_case($train->OriginName, MB_CASE_TITLE);
            $routeOriginNameEn = $routeOriginStation ? ($routeOriginStation->city ? $routeOriginStation->city->nameEn : mb_convert_case($routeOriginStation->nameEn,
                MB_CASE_TITLE)) : mb_convert_case(StringHelpers::slug($train->OriginName, false), MB_CASE_TITLE);

            $routeDestinationNameRu = $routeDestinationStation ? ($routeDestinationStation->city ? $routeDestinationStation->city->nameRu : mb_convert_case($routeDestinationStation->nameRu,
                MB_CASE_TITLE)) : mb_convert_case($train->DestinationName, MB_CASE_TITLE);
            $routeDestinationNameEn = $routeDestinationStation ? ($routeDestinationStation->city ? $routeDestinationStation->city->nameEn : mb_convert_case($routeDestinationStation->nameEn,
                MB_CASE_TITLE)) : mb_convert_case(StringHelpers::slug($train->DestinationName, false), MB_CASE_TITLE);

            $nameSlug = 'name' . mb_convert_case(app()->getLocale(), MB_CASE_TITLE);

            $routeNames = [
                'nameRu' => $routeOriginNameRu . ' − ' . $routeDestinationNameRu,
                'nameEn' => $routeOriginNameEn . ' − ' . $routeDestinationNameEn
            ];

            if (count($carsList) > 0) {

                $carriers = [];

                if (isset($train->Carriers)) {
                    foreach ($train->Carriers as $carrier) {
                        if (LangHelper::trans('railway/carriers.' . StringHelpers::slug($carrier))) $carriers[$carrier] = LangHelper::trans('railway/carriers.' . StringHelpers::slug($carrier));
                    }
                }

                $compactList[] = [
                    'Id' => $train->Id,
                    'displayTrainNumber' => $train->DisplayTrainNumber,
                    'trainNumberToGetRoute' => $train->TrainNumberToGetRoute,
                    'trainNumber' => $train->TrainNumberToGetRoute,
                    'ER' => $train->HasElectronicRegistration,
                    'trainName' => $train->TrainName,
                    'trainDescription' => $train->TrainDescription,
                    'trainDescriptionTitle' => LangHelper::trans('railway/trainDescription.' . StringHelpers::slug($train->TrainDescription)),
                    'trainRouteRu' => $routeOriginNameRu . ' − ' . $routeDestinationNameRu,
                    'trainRouteEn' => $routeOriginNameEn . ' − ' . $routeDestinationNameEn,
                    'trainRoute' => $routeNames[$nameSlug],
                    'carriers' => $train->Carriers,
                    'carrierTitles' => $carriers,
                    'departure' => [
                        'stationRu' => !empty($originStation->custom->nameRu) ? $originStation->custom->nameRu : $originStation->nameRu,
                        'stationEn' => !empty($originStation->custom->nameEn) ? $originStation->custom->nameEn : $originStation->nameEn,
                        'stationName' => $originStation->name,
                        'stationCode' => $originStation->code,
                        'cityRu' => $originStationCity->nameRu ?? '',
                        'cityEn' => $originStationCity->nameEn ?? '',
                        'cityName' => $originStationCity->name ?? '',
                        'cityCode' => $originStationCity->code ?? '',
                        'dateEn' => date('Y-m-d', strtotime($train->LocalDepartureDateTime)),
                        'dateRu' => date('d.m.Y', strtotime($train->LocalDepartureDateTime)),
                        'time' => date('H:i', strtotime($train->LocalDepartureDateTime)),
                        'sortTime' => strtotime($train->LocalDepartureDateTime),
                        'datetime' => $train->DepartureDateTime,
                        'datetimeLocal' => $train->LocalDepartureDateTime
                    ],
                    'arrival' => [
                        'stationRu' => !empty($arrivalStation->custom->nameRu) ? $arrivalStation->custom->nameRu : $arrivalStation->nameRu,
                        'stationEn' => !empty($arrivalStation->custom->nameEn) ? $arrivalStation->custom->nameEn : $arrivalStation->nameEn,
                        'stationName' => $arrivalStation->name,
                        'stationCode' => $arrivalStation->code,
                        'cityRu' => $arrivalStationCity->nameRu ?? '',
                        'cityEn' => $arrivalStationCity->nameEn ?? '',
                        'cityName' => $arrivalStationCity->$nameSlug ?? '',
                        'cityCode' => $arrivalStationCity->code ?? '',
                        'dateEn' => date('Y-m-d', strtotime($train->LocalArrivalDateTime)),
                        'dateRu' => date('d.m.Y', strtotime($train->LocalArrivalDateTime)),
                        'time' => date('H:i', strtotime($train->LocalArrivalDateTime)),
                        'sortTime' => strtotime($train->LocalArrivalDateTime),
                        'datetime' => $train->ArrivalDateTime,
                        'datetimeLocal' => $train->LocalArrivalDateTime
                    ],
                    'crossDay' => date('Y-m-d', strtotime($train->DepartureDateTime)) != date('Y-m-d',
                            strtotime($train->ArrivalDateTime)),
                    'tripDuration' => $train->TripDuration,
                    'duration' => [
                        'd' => (int)((int)($train->TripDuration / 60) / 24),
                        'h' => ((int)($train->TripDuration / 60 % 24)),
                        'm' => ($train->TripDuration % 60)
                    ],
                    'places' => $carsList,
                    'placeTypesSummary' => $carsPlacesSummary,
                    'services' => $services,
                    'sort' => strtotime($train->LocalDepartureDateTime)
                ];
            }
        }

        usort($compactList, function ($a, $b) {
            return $a['sort'] <=> $b['sort'];
        });

        return $compactList;
    }

    /**
     * Маппинг метод результата CarPricing
     * @param array $data
     * @param array $options
     * @return array
     */
    protected static function mapCarPricing($data, $options = [])
    {
        $data = StringHelpers::ObjectToArray($data);
        $carPricingFormatter = new CarPricingFormatter();

        if (!empty($data['Cars'])) {
            $cars = new Collection($data['Cars']);

            $data['CarsV2'] = $cars
                ->mapToGroups(function ($item) {
                    if ($item['ServiceClass']) {
                        return [$item['ServiceClass'] => $item];
                    } elseif ($item['InternationalServiceClass']) {
                        return [$item['InternationalServiceClass'] => $item];
                    }
                    return ['Baggage' => $item];
                })
                ->forget('Baggage')
                ->map(function (Collection $car) use ($carPricingFormatter) {
                    return $car->groupBy('HasGenderCabins')->map(function (Collection $currentCars) use (
                        $carPricingFormatter
                    ) {
                        return $carPricingFormatter($currentCars);
                    })->values();
                });
            $carParams = $data['CarsV2']->flatten(1);
            $data['HasElectronicRegistration'] = $carParams->contains('HasElectronicRegistration', 'true');
            $data['IsTwoStorey'] = $carParams->contains('IsTwoStorey', 'true');
            $data['carrier'] = $carParams->pluck('carrier')->unique();
            $settings = new Settings();
            $totalTax = (float)$settings->get('taxRailwayImRzdPurchase') + (float)$settings->get('taxRailwayImTrivagoPurchase');

            //пока убрал таксу
            // $data['CarsV2']['tax'] = [
            // 'rzhd' => (float)$settings->get('taxRailwayImRzdPurchase'),
            //  'agent' => (float)$settings->get('taxRailwayImTrivagoPurchase'),
            //  'total' => $totalTax
            // ];

            foreach ($data['Cars'] as &$car) {
                $car['MinPrice'] = isset($car['MinPrice']) && $car['MinPrice'] > 0 ? (float)$car['MinPrice'] + $totalTax : null;
                $car['MaxPrice'] = isset($car['MaxPrice']) && $car['MaxPrice'] > 0 ? (float)$car['MaxPrice'] + $totalTax : null;
            }
        }

        unset($data['Cars']);
        unset($data['TrainInfo']);
        unset($data['HasElectronicRegistration']);

        return $data;
    }

    protected static function mapSchedule($data, $options = [])
    {
        $compactList = [];
        foreach ($data->Schedules as $id => $train) {
            $originStation = RailwayStation::where('code', $train->OriginStationCode)->with('city')->first();
            $arrivalStation = RailwayStation::where('code', $train->DestinationStationCode)->with('city')->first();

            $originStationCity = $originStation->city;
            $arrivalStationCity = $arrivalStation->city;

            if (!$originStationCity) {
                $originStationCity = City::where('info->expressCode', $train->OriginStationCode)->first();
            }
            if (!$originStationCity) {
                $originStationCity = $originStation->region;
            }

            if (!$arrivalStationCity) {
                $arrivalStationCity = City::where('info->expressCode', $train->DestinationStationCode)->first();
            }
            if (!$arrivalStationCity) {
                $arrivalStationCity = $arrivalStation->region;
            }

            $destinationStation = null;//RailwayStation::byKeyword(explode(' ',$train->DestinationName)[0])->with('city')->first();

            $trainDepartureTime = strtotime($options['departureDate'] . 'T' . $train->LocalDepartureTime . ':00' . ' ' . (3 - $originStation->info->utcTimeOffset) . ' hour');
            $trainDepartureLocalTime = strtotime($options['departureDate'] . 'T' . $train->LocalDepartureTime . ':00');
            $trainArrivalTime = strtotime($options['departureDate'] . 'T' . $train->LocalDepartureTime . ':00 +' . $train->TripDuration . ' minutes ' . (3 - $originStation->info->utcTimeOffset) . ' hour');
            $trainArrivalLocalTime = strtotime($options['departureDate'] . 'T' . $train->LocalDepartureTime . ':00 +' . $train->TripDuration . ' minutes ' . (3 - $originStation->info->utcTimeOffset) . ' hour');

            $trainName = '';

            if (!$train->TrainName) {
                $trainBase = Trains::where('trainNumber', $train->TrainNumber)->select('trainName')->first();
                if ($trainBase) {
                    $trainName = $trainBase->trainName;
                }
            }

            $originNameArr = explode(' ', $train->OriginName);
            $destinationNameArr = explode(' ', $train->DestinationName);

            $routeTrain = Trains::where('trainNumber', $train->TrainNumberToGetRoute)->first();

            $routeOriginStation = false;
            $routeDestinationStation = false;

            if ($routeTrain && $routeTrain->originStationCode !== '') {
                $routeOriginStation = $routeTrain->originStation()->with('city')->first();
                $routeDestinationStation = $routeTrain->destinationStation()->with('city')->first();
            }

            if (!$routeOriginStation) {
                $routeOriginStation = RailwayStation::byName($train->OriginName)->with('city')->first();
            }
            if (!$routeOriginStation) {
                $routeOriginStation = RailwayStation::byName($train->OriginName)->orWhere('nameRu', 'ilike',
                    $originNameArr[0] . '%')->with('city')->first();
            }

            if (!$routeOriginStation) {
                $routeDestinationStation = RailwayStation::byName($train->DestinationName)->with('city')->first();
            }
            if (!$routeDestinationStation) {
                $routeDestinationStation = RailwayStation::byName($train->DestinationName)->orWhere('nameRu', 'ilike',
                    $destinationNameArr[0] . '%')->with('city')->first();
            }

            $routeOriginNameRu = $routeOriginStation ? ($routeOriginStation->city ? $routeOriginStation->city->nameRu : mb_convert_case($routeOriginStation->nameRu,
                MB_CASE_TITLE)) : mb_convert_case($train->OriginName, MB_CASE_TITLE);
            $routeOriginNameEn = $routeOriginStation ? ($routeOriginStation->city ? $routeOriginStation->city->nameEn : mb_convert_case($routeOriginStation->nameEn,
                MB_CASE_TITLE)) : mb_convert_case(StringHelpers::slug($train->OriginName, false), MB_CASE_TITLE);

            $routeDestinationNameRu = $routeDestinationStation ? ($routeDestinationStation->city ? $routeDestinationStation->city->nameRu : mb_convert_case($routeDestinationStation->nameRu,
                MB_CASE_TITLE)) : mb_convert_case($train->DestinationName, MB_CASE_TITLE);
            $routeDestinationNameEn = $routeDestinationStation ? ($routeDestinationStation->city ? $routeDestinationStation->city->nameEn : mb_convert_case($routeDestinationStation->nameEn,
                MB_CASE_TITLE)) : mb_convert_case(StringHelpers::slug($train->DestinationName, false), MB_CASE_TITLE);

            $nameSlug = 'name' . mb_convert_case(app()->getLocale(), MB_CASE_TITLE);

            $routeNames = [
                'nameRu' => $routeOriginNameRu . ' − ' . $routeDestinationNameRu,
                'nameEn' => $routeOriginNameEn . ' − ' . $routeDestinationNameEn
            ];

            $compactList[] = [
                'Id' => $id + 1,
                'displayTrainNumber' => $train->TrainNumber,
                'trainNumberToGetRoute' => $train->TrainNumberToGetRoute,
                'trainNumber' => $train->TrainNumberToGetRoute,
                'ER' => false,
                'trainName' => $trainName ?? '',
                'trainDescription' => '',
                'trainRouteRu' => $routeOriginNameRu . ' − ' . $routeDestinationNameRu,
                'trainRouteEn' => $routeOriginNameEn . ' − ' . $routeDestinationNameEn,
                'trainRoute' => $routeNames[$nameSlug],
                'carriers' => [],
                'departure' => [
                    'stationRu' => !empty($originStation->custom->nameRu) ? $originStation->custom->nameRu : $originStation->nameRu,
                    'stationEn' => !empty($originStation->custom->nameEn) ? $originStation->custom->nameEn : $originStation->nameEn,
                    'stationName' => $originStation->name,
                    'stationCode' => $originStation->code,
                    'cityRu' => $originStationCity->nameRu ?? '',
                    'cityEn' => $originStationCity->nameEn ?? '',
                    'cityName' => $originStationCity->name ?? '',
                    'cityCode' => $originStationCity->code ?? '',
                    'dateEn' => date('Y-m-d', $trainDepartureLocalTime),
                    'dateRu' => date('d.m.Y', $trainDepartureLocalTime),
                    'time' => date('H:i', $trainDepartureLocalTime),
                    'sortTime' => $trainDepartureLocalTime,
                    'datetime' => date('Y-m-d\TH:i:s', $trainDepartureTime),
                    'datetimeLocal' => date('Y-m-d\TH:i:s', $trainDepartureLocalTime),
                ],
                'arrival' => [
                    'stationRu' => !empty($arrivalStation->custom->nameRu) ? $arrivalStation->custom->nameRu : $arrivalStation->nameRu,
                    'stationEn' => !empty($arrivalStation->custom->nameEn) ? $arrivalStation->custom->nameEn : $arrivalStation->nameEn,
                    'stationName' => $arrivalStation->name,
                    'stationCode' => $arrivalStation->code,
                    'cityRu' => $arrivalStationCity->nameRu ?? '',
                    'cityEn' => $arrivalStationCity->nameEn ?? '',
                    'cityName' => $arrivalStationCity->name ?? '',
                    'cityCode' => $arrivalStationCity->code ?? '',
                    'dateEn' => date('Y-m-d', $trainArrivalLocalTime),
                    'dateRu' => date('d.m.Y', $trainArrivalLocalTime),
                    'time' => date('H:i', $trainArrivalLocalTime),
                    'sortTime' => $trainArrivalLocalTime,
                    'datetime' => date('Y-m-d\TH:i:s', $trainArrivalTime),
                    'datetimeLocal' => date('Y-m-d\TH:i:s', $trainArrivalLocalTime),
                ],
                'crossDay' => date('Y-m-d', $trainDepartureTime) != date('Y-m-d', $trainArrivalTime),
                'tripDuration' => $train->TripDuration,
                'duration' => [
                    'd' => (int)((int)($train->TripDuration / 60) / 24),
                    'h' => ((int)($train->TripDuration / 60 % 24)),
                    'm' => ($train->TripDuration % 60)
                ],
                'places' => [],
                'placeTypesSummary' => $carsPlacesSummary = [
                    'lowerQnt' => 0,
                    'upperQnt' => 0,
                    'lowerSideQnt' => 0,
                    'upperSideQnt' => 0,
                    'maleQnt' => 0,
                    'femaleQnt' => 0,
                    'mixedQnt' => 0,
                ],
                'services' => []
            ];
        }
        return $compactList;
    }

    protected static function mapTrainRoute($data, $options = [])
    {
        $departureDate = $options['DepartureDate'] ?? date('Y-m-d\T00:00:00');

        $output = [];
        $days = 0;
        $prevTime = '00:00';

        foreach ($data->Routes[0]->RouteStops as $routeStop) {
            $departureAddDay = 0;
            $originStation = RailwayStation::where('code', $routeStop->StationCode)->with('city')->first();

            $originStationCity = $originStation->city;

            if (!$originStationCity) {
                $originStationCity = City::where('info->expressCode', $routeStop->StationCode)->first();
            }
            if (!$originStationCity) {
                $originStationCity = $originStation->region;
            }

            $arrivalTime = $routeStop->ArrivalTime == '' ? date('H:i',
                strtotime($routeStop->DepartureTime . ' - 30 minutes')) : $routeStop->ArrivalTime;

            if ($prevTime > $arrivalTime) {
                $days++;
            }
            $prevTime = $arrivalTime;

            if ($arrivalTime > $routeStop->DepartureTime) {
                $departureAddDay = 1;
            }
            $departureDays = $days + $departureAddDay;

            $output['stops'][] = [
                'station' => [
                    'nameRu' => !empty($originStation->custom->nameRu) ? $originStation->custom->nameRu : $originStation->nameRu,
                    'nameEn' => !empty($originStation->custom->nameEn) ? $originStation->custom->nameEn : $originStation->nameEn,
                ],
                'city' => [
                    'nameRu' => $originStationCity->nameRu ?? '',
                    'nameEn' => $originStationCity->nameEn ?? ''
                ],
                'arrivalDate' => $routeStop->ArrivalTime != '' ? date('Y-m-d',
                    strtotime($departureDate . "+ {$days} day")) : '',
                'arrivalTime' => $routeStop->ArrivalTime,
                'localArrivalTime' => $routeStop->LocalArrivalTime,
                'departureDate' => $routeStop->DepartureTime != '' ? date('Y-m-d',
                    strtotime($departureDate . "+ {$departureDays} day")) : '',
                'departureTime' => $routeStop->DepartureTime,
                'localDepartureTime' => $routeStop->LocalDepartureTime,
                'stopDuration' => $routeStop->StopDuration
            ];
        }

        return $output;
    }
}
