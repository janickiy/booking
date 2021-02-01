<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use App\Jobs\AddTrainsFromSearch;
use App\Models\References\Trains;
use App\Models\References\TrainsCar;
use App\Models\References\RailwayStation;
use App\Services\External\InnovateMobility\v1\RailwaySearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Railway Pricing & Info
 * Class RailwayController
 * @package App\Http\Controllers\Api
 */
class RailwayController extends Controller
{
    /**
     * Train Pricing
     * [Посик поездов и цен со свободными местами]
     *
     * @bodyParam date string required Дата поездки
     * @bodyParam from string required Код станции отправления
     * @bodyParam to string required Код станции прибытия
     * @bodyParam time string required Временной промежуток отправления в часах (по умолчанию 0-24)
     * @bodyParam displayZeroPlaces int required Показывать поезда на которые нет мест (не реализовано)
     * @bodyParam dateBack string Дата обратной поездки
     *
     * @response {
     * "from": {},
     * "to": {},
     * "date": "28.02.2019",
     * "dateBack": "01.03.2019",
     * "trains": [],
     * "trainsBack": []
     * }
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function search(Request $request)
    {

        $originCity = RailwayStation::where('code', $request->input('from'))->with(['city', 'city.country'])->first();
        $destinationCity = RailwayStation::where('code', $request->input('to'))->with([
            'city',
            'city.country'
        ])->first();

        $response = [
            'stations' => [
                'from' => $originCity ? $originCity : null,
                'to' => $destinationCity ? $destinationCity : null
            ],
            'dates' => [
                'to' => date('Y-m-d\TH:i:s', strtotime($request->get('date'))),
                'back' => $request->get('dateBack') ? date('Y-m-d\TH:i:s', strtotime($request->get('dateBack'))) : null
            ],
            'trains' => [
                'to' => null,
                'back' => null
            ],
            'message' => null

        ];

        if (strtotime($request->get('date') . ' 23:59:59') < time()) {
            $response['message'] = 'Неверная дата отправления';
            return ResponseHelpers::jsonResponse($response, 400);
        }

        if (!$originCity || !$destinationCity) {
            $response['message'] = trans('references/im.railwayErrorCodes.316');
            return ResponseHelpers::jsonResponse($response, 400);
        }

        $time = isset($request->time) ? explode('-', $request->input('time')) : null;

        if ($originCity->cityId == 10650) {

            $options = [
                "Origin" => $request->input('from'),
                "Destination" => $request->input('to'),
                "DepartureDate" => date('Y-m-d', strtotime($request->input('date'))) . 'T00:00:00',
                "TimeFrom" => isset($time[0]) ? $time[0] : null,
                "TimeTo" => isset($time[1]) ? $time[1] : null,
            ];

            $result = RailwaySearch::getSchedule($options, true,
                ['departureDate' => date('Y-m-d', strtotime($request->input('date')))]);

        } else {

            $options = [
                "Origin" => $request->input('from'),
                "Destination" => $request->input('to'),
                "DepartureDate" => date('Y-m-d', strtotime($request->input('date'))) . 'T00:00:00',
                "TimeFrom" => isset($time[0]) ? $time[0] : null,
                "TimeTo" => isset($time[1]) ? $time[1] : null,
                "CarGrouping" => "Group"
            ];

            $result = RailwaySearch::getTrainPricing(
                $options, true,
                ['allowZeroPlace' => $request->input('displayZeroPlaces', false)]
            );

            if (isset($originCity->info->utcTimeOffset) && $originCity->info->utcTimeOffset != 3) {

                $additionalDateTime = strtotime($request->input('date') . ' 00:00:00 ' . (3 - $originCity->info->utcTimeOffset) . ' hour');
                $options['DepartureDate'] = date('Y-m-d', $additionalDateTime) . 'T00:00:00';
                $options['TimeFrom'] = date('H', $additionalDateTime);
                $addResult = RailwaySearch::getTrainPricing(
                    $options, true,
                    ['allowZeroPlace' => $request->input('displayZeroPlaces', false)]
                );

                if ($addResult && $result) {
                    $result = array_merge($addResult, $result);
                }
            }
        }

        $result2 = false;
        $dateBack = $request->input('dateBack', false);

        if ($dateBack && $dateBack != '') {
            if ($destinationCity->cityId == 10650) {
                $optionsBack = [
                    "Origin" => $request->input('to'),
                    "Destination" => $request->input('from'),
                    "DepartureDate" => date('Y-m-d', strtotime($request->input('dateBack'))) . 'T00:00:00',
                    "TimeFrom" => isset($time[0]) ? $time[0] : null,
                    "TimeTo" => isset($time[1]) ? $time[1] : null,
                ];

                $result2 = RailwaySearch::getSchedule($optionsBack, true,
                    ['departureDate' => date('Y-m-d', strtotime($request->input('dateBack')))]);
            } else {
                $optionsBack = [
                    "Origin" => $request->input('to'),
                    "Destination" => $request->input('from'),
                    "DepartureDate" => date('Y-m-d', strtotime($request->input('dateBack'))) . 'T00:00:00',
                    "TimeFrom" => isset($time[0]) ? $time[0] : null,
                    "TimeTo" => isset($time[1]) ? $time[1] : null,
                    "CarGrouping" => "Group"
                ];
                $result2 = RailwaySearch::getTrainPricing(
                    $optionsBack, true,
                    ['allowZeroPlace' => $request->input('displayZeroPlaces', false)]
                );
            }
        }

        if (!$result) {
            $error = RailwaySearch::getLastError();
            $response['message'] = $error->Message;
            $code = 500;
            if (in_array($error->Code, [311, 310, 312, 313, 314, 316, 317, 318, 301])) {
                $code = 200;
            }
            return ResponseHelpers::jsonResponse($response, $code);
        }

        $history = $request->session()->get('railwayHistory', []);

        $key = md5(json_encode([
            'from' => $originCity,
            'to' => $destinationCity,
            'date' => $request->input('date'),
            'dateBack' => $dateBack,
        ]));

        $history[$key] = [
            'from' => $originCity,
            'to' => $destinationCity,
            'date' => date('Y-m-d\TH:i:s', strtotime($request->input('date'))),
            'dateBack' => $dateBack ? date('Y-m-d\TH:i:s', strtotime($dateBack)) : null,
        ];

        $request->session()->put('railwayHistory', $history);

        //trainNumberToGetRoute

        if ($result) {
            $this->addTrains($result);
        }
        if ($result2) {
            $this->addTrains($result2);
        }

        $response['trains']['to'] = $result;
        $response['trains']['back'] = $result2 ? $result2 : null;

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Train route
     * [Маршрут поезда со всеми станциями]
     * @bodyParam date string required Дата поездки
     * @bodyParam from string required Код станции отправления
     * @bodyParam to string required Код станции прибытия
     * @bodyParam trainNumber string required Номер поезда для получения маршрута
     *
     * @response {
     * "from":{},
     * "to":{},
     * "routes":[]
     * }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getRoute(Request $request)
    {
        $originCity = RailwayStation::where('code', $request->input('from'))->with(['city', 'city.country'])->first();
        $destinationCity = RailwayStation::where('code', $request->input('to'))->with([
            'city',
            'city.country'
        ])->first();

        if (!isset($originCity)) {
            return ResponseHelpers::jsonResponse([
                'error' => 'Код станции отправления не найден'
            ], 404);
        }

        if (!isset($destinationCity)) {
            return ResponseHelpers::jsonResponse([
                'error' => 'Код станции назначения не найден'
            ], 404);
        }

        $date = date('Y-m-d\T00:00:00', strtotime($request->input('date')));

        $options = [
            "TrainNumber" => $request->input('trainNumber'),
            "Origin" => $request->input('from'),
            "Destination" => $request->input('to'),
            "DepartureDate" => $date,
        ];

        $result = RailwaySearch::getTrainRoute($options, true, ["DepartureDate" => $date]);

        if (!$result) {
            return ResponseHelpers::jsonResponse([
                'error' => RailwaySearch::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse([
            'trainNumber' => $request->input('trainNumber'),
            'route' => $result
        ]);
    }

    /**
     * Car Pricing
     * [Получение списка доступных вагонов и мест для конкретного поезда и даты]
     * @bodyParam date string required Дата поездки
     * @bodyParam from string required Код станции отправления
     * @bodyParam to string required Код станции прибытия
     * @bodyParam trainCode string required Номер поезда
     * @bodyParam search array required Параметры поиска {"from":"2000000","to":"2004000","date":"28.02.2019","dateBack":"01.03.2019"}
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCarPricing(Request $request)
    {

        $originCity = RailwayStation::where('code', $request->input('from'))->with(['city', 'city.country'])->first();
        $destinationCity = RailwayStation::where('code', $request->input('to'))->with([
            'city',
            'city.country'
        ])->first();

        $date = $request->get('date');
        $isoDate = date('Y-m-d\TH:i:s', strtotime($date));

        $response = [
            'stations' => [
                'from' => $originCity ? $originCity : null,
                'to' => $destinationCity ? $destinationCity : null
            ],
            'date' => $isoDate,
            'cars' => null,
            'schemes' => null,
            'train' => null,
            'message' => null

        ];

        $OriginCode = $originCity->code ?? false;
        $DestinationCode = $destinationCity->code ?? false;


        if (!$originCity || !$destinationCity || $OriginCode===$DestinationCode) {
            $response['message'] = trans('references/im.railwayErrorCodes.316');
            return ResponseHelpers::jsonResponse($response, 400);
        }


        if (strtotime($date . ' 23:59:59') < time()) {
            $response['message'] = 'Неверная дата отправления';
            return ResponseHelpers::jsonResponse($response, 400);
        }

        $cars = RailwaySearch::doCarPricing([
            "TrainNumber" => $request->input('trainCode'),
            "OriginCode" => $OriginCode,
            "DestinationCode" => $DestinationCode,
            "DepartureDate" => $isoDate,
        ], true);

        if (!$cars) {
            $error = RailwaySearch::getLastError();
            $response['message'] = $error->Message;
            $code = 500;

            if (in_array($error->Code, [311, 310, 312, 313, 314, 316, 317, 318, 301])) {
                $code = 200;
            }elseif(in_array($error->Code, [41,42,43,44,48,51])){
                $code = 400;
            }

            return ResponseHelpers::jsonResponse($response, $code);
        }

        if ($originCity->cityId == 10650) {
            $train = RailwaySearch::getSchedule(
                [
                    "Origin" => $OriginCode,
                    "Destination" => $DestinationCode,
                    "DepartureDate" => $isoDate,
                    "TimeFrom" => null,
                    "TimeTo" => null,
                ],
                true,
                ['departureDate' => date('Y-m-d', strtotime($date))]
            );
        }else{
            $train =  RailwaySearch::getTrainPricing(
                [
                    "Origin" => $OriginCode,
                    "Destination" => $DestinationCode,
                    "DepartureDate" => $isoDate,
                    "TimeFrom" => null,
                    "TimeTo" => null,
                ],
                true
            );
        }

        if (isset($train) && $train) {
            $array_trains = array_filter($train, function ($k) use ($request) {
                return $k['trainNumber'] == $request->trainCode;
            });
            $array_trains = array_pop($array_trains);
        }

        $response['cars'] = $cars;
        $response['train'] = $array_trains ?? null;
        $response['schemes'] = $this->getSchemes($request->input('trainCode'), true);

        return ResponseHelpers::jsonResponse($response);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getRoutePricing(Request $request)
    {
        $options = [
            "OriginCode" => $request->input('OriginCode'),
            "DestinationCode" => $request->input('DestinationCode'),
            "DepartureDate" => $request->input('DepartureDate') . 'T00:00:00',
        ];

        $result = RailwaySearch::getRoutePricing($options);

        if (!$result) {
            return ResponseHelpers::jsonResponse([
                'error' => RailwaySearch::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($result);
    }

    /**
     * Get history
     * [Получение истории поиска]
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getHistory(Request $request)
    {
        $history = $request->session()->get('railwayHistory', []);

        $output = [];
        foreach ($history as $item) {
            $output[] = [
                'stations' => [
                    'to' => $item['to'],
                    'from' => $item['from']
                ],
                'dates' => [
                    'to' => $item['date'],
                    'back' => $item['dateBack'] ? $item['dateBack'] : null
                ]
            ];
        }

        return ResponseHelpers::jsonResponse($output);
    }

    /**
     * Delete history
     * [Очистка истории поиска]
     * @param Request $request
     */
    public function deleteHistory(Request $request)
    {
        $request->session()->forget('railwayHistory');
    }

    /**
     * @param array $trains
     */
    public function addTrains($trains = array())
    {
        AddTrainsFromSearch::dispatch($trains);
    }

    /**
     * Train schemes
     * [Получение схем вагонов для поезда]
     * @bodyParam trainNumber string Номер поезда
     * @bodyParam raw boolean Возвращать схему вагона в теле ответа
     * @TODO: fill responses
     * @response 200
     * @response 400
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse || array
     */
    public function trainSchemes(Request $request)
    {
        $trainNumber = $request->get('trainNumber', null);
        $returnRaw = (bool)$request->get('raw', false);


        if (!$trainNumber) {
            response()->json('', 400);
        }

        $result = [
            'info' => [
                'trainNumber' => $trainNumber,
            ],
            'schemes' => []
        ];

        $result['schemes'] = $this->getSchemes($trainNumber, $returnRaw);


        return response()->json($result, 200);
    }

    private function getSchemes($trainNumber, $returnRaw)
    {
        $storageMethod = $returnRaw ? 'get' : 'url';
        $trainName = false;

        $train = Trains::where('trainNumber', $trainNumber)->first();

        if ($train) {
            $trainName = $train->trainName;
        }


        $trainCars = TrainsCar::whereHas('trains', function ($query) use ($trainNumber) {
            $query->where('trainNumber', $trainNumber);
        })->whereNotNull('schemes')->with('trains')->get();

        if ($trainCars->count() < 1 && $trainName !== false) {
            $trainCars = TrainsCar::where('trainName', 'ilike', $trainName)->whereNotNull('schemes')->get();
        }

        $result['schemes'] = [];

        $defaultsTrainCars = TrainsCar::doesntHave('trains')->where('trainName', '')->get();

        // Сначала заполняем дефолтными значениями
        foreach ($defaultsTrainCars as $trainCar) {
            $result['schemes'][$trainCar->typeEn] = [];
            if (!$trainCar->schemes) {
                continue;
            }
            foreach ($trainCar->schemes as $key => $schemeUrl) {
                $result['schemes'][$trainCar->typeEn][$key] = Storage::$storageMethod($schemeUrl);
            }
        }

        // Затем если есть конкретные схемы - заполняем массив ими
        foreach ($trainCars as $trainCar) {
            if (!isset($result['schemes'][$trainCar->typeEn])) {
                $result['schemes'][$trainCar->typeEn] = [];
            }
            if (!$trainCar->schemes) {
                continue;
            }

            foreach ($trainCar->schemes as $key => $schemeUrl) {
                $result['schemes'][$trainCar->typeEn][$key] = Storage::$storageMethod($schemeUrl);
            }
        }

        return $result['schemes'];
    }

    /**
     * Old train scheme
     * [Получение схем вагонов для поезда]
     * @bodyParam trainNumber string Номер поезда
     * @bodyParam typeScheme string Тип вагона
     * @bodyParam raw boolean Возвращать схему вагона в теле ответа
     * @deprecated
     *
     * @response [
     * {
     * "typeScheme":"",
     * "url_scheme":"",
     * "trainNumber":"",
     * "trainNumberToGetRoute":"",
     * "scheme_raw":""
     * }
     * ]
     *
     * @response 404 [
     * "false"
     * ]
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function old_trainScheme(Request $request)
    {
        $trainNumber = $request->get('trainNumber', null);
        $typeScheme = $request->get('typeScheme', null);
        $returnRaw = (bool)$request->get('raw', false);

        if (!$trainNumber) {
            return ResponseHelpers::jsonResponse('', 400);
        }

        $query = TrainsCar::whereHas('trains', function ($query) use ($trainNumber) {
            $query->where('trainNumber', $trainNumber);
        })->with('trains');

        if ($typeScheme) {
            $query->where('typeScheme', $typeScheme);
        }
        $trainCars = $query->get();

        $result = [];
        foreach ($trainCars as $car) {
            $carData = [
                'typeScheme' => $car->typeScheme,
                'schemes' => $car->schemes,
                'trainNumber' => $car->trains->trainNumber,
                'trainNumberToGetRoute' => $car->trains->trainNumberToGetRoute,
            ];

            if ($returnRaw) {
                $carData['schemeRaw'] = ($car->scheme && Storage::exists($car->scheme)) ? Storage::get($car->scheme) : '';
            }

            $result[] = $carData;
        }

        return ResponseHelpers::jsonResponse($result, $result ? 200 : 404);
    }
}