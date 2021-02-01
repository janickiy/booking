<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use App\Models\References\Airport; // TODO сделать работу через адаптер + рефактор
use App\Models\References\City;
use Carbon\Carbon;

class SearchResultsHelper
{
    /**
     * Возможные варианты значений поля refundable и baggage:
     * - null: параметр не применим к данной тарифной опции
     * - notAvailable: услуга не доступна
     * - free: услуга доступна, является бесплатной
     * - charge: услуга доступна за дополнительную плату
     *
     * @param \RK_Avia_Entity_Search_Request $request
     * @param $response
     * @return array
     * @throws \RK_Core_Exception
     */
    public static function searchResponseToJSON($request, $response)
    {
        $resultJSON = [];

        /** @var \RK_Avia_Entity_Booking $item */
        foreach ($response as $itemKey => $item) {
            // Сегменты
            $segments = $item->getSegments();
            $segmentsArr = [];
            if (is_array($segments)) {
                foreach ($segments as $segmentNum => $segment) {
                    // Аэропорт/город отправления
                    $origin = Airport::where('code', $segment->getDepartureCode())->with(['city', 'country'])->first();
                    if (!$origin) {
                        $origin = City::where('code', $segment->getDepartureCode())->with(['country'])->first();
                    }

                    // Аэропорт/город прибытия
                    $destination = Airport::where('code', $segment->getArrivalCode())->with(['city', 'country'])->first();
                    if (!$destination) {
                        $destination = City::where('code', $segment->getArrivalCode())->with(['country'])->first();
                    }

                    // Форматирование времени перелета
                    $flightTimeH = floor($segment->getFlightTime() / 60);
                    $flightTimeH = $flightTimeH . ' ' . trans_choice('frontend.hour', $flightTimeH);
                    $flightTimeM = $segment->getFlightTime() % 60;
                    $flightTimeM = $flightTimeM . ' ' . trans_choice('frontend.minute', $flightTimeM);

                    // Расчет времени пересадки между сегментами
                    if ($item->getSegment($segmentNum + 1) &&
                        $item->getSegment($segmentNum + 1)->getWayNumber() === $item->getSegment($segmentNum)->getWayNumber()) {

                        // Время пересадки
                        $currentSegment = $item->getSegment($segmentNum);
                        $nextSegment = $item->getSegment($segmentNum + 1);

                        $arrivalDateTime = $currentSegment->getArrivalDate()->getDateTime();
                        $departureDateTime = $nextSegment->getDepartureDate()->getDateTime();

                        $transferDiff = $departureDateTime->diff($arrivalDateTime);

                        $item->getSegment($segmentNum)->setTransferTime($transferDiff);
                    }

                    // Время пересадки
                    $info = [
                        'isTransfer' => $segment->isTransfer()
                    ];

                    if ($segment->isTransfer()) {
                        $info['transferTime'] = $segment->getTransferTime()->format('%h часов %I минут');
                    }

                    $segmentsArr[$segment->getWayNumber()][] = [
                        'origin' => $origin,
                        'destination' => $destination,
                        'originTerminal' => $segment->getDepartureTerminal(),
                        'destinationTerminal' => $segment->getArrivalTerminal(),
                        'departureDate' => [
                            'date' => $segment->getDepartureDate()->getValue('Y-m-d'),
                            'time' => $segment->getDepartureDate()->getValue('H:i')
                        ],
                        'arrivalDate' => [
                            'date' => $segment->getArrivalDate()->getValue('Y-m-d'),
                            'time' => $segment->getArrivalDate()->getValue('H:i')
                        ],
                        'departureDateFormatted' => [
                            'date' => Carbon::createFromFormat('Y-m-d H:i', $segment->getDepartureDate()->getValue('Y-m-d H:i'))->formatLocalized('%d %B %Y, %a'),
                            'time' => Carbon::createFromFormat('Y-m-d H:i', $segment->getDepartureDate()->getValue('Y-m-d H:i'))->formatLocalized('%H:%M')
                        ],
                        'arrivalDateFormatted' => [
                            'date' => Carbon::createFromFormat('Y-m-d H:i', $segment->getArrivalDate()->getValue('Y-m-d H:i'))->formatLocalized('%d %B %Y, %a'),
                            'time' => Carbon::createFromFormat('Y-m-d H:i', $segment->getArrivalDate()->getValue('Y-m-d H:i'))->formatLocalized('%H:%M'),
                        ],
                        'flightNumber' => $segment->getFlightNumber(),
                        'equipment' => $segment->getAircraftCode(),
                        'flightTime' => $segment->getFlightTime(),
                        'flightTimeFormatted' => $flightTimeH . ' ' . $flightTimeM,
                        'airline' => $segment->getOperationCarrierCode(),
                        'classOfService' => $segment->getSubClass(),
                        'cabinClass' => $segment->getBaseClass(),
                        'typeClass' => $segment->getTypeClass() ? $segment->getTypeClass() : FareFamilies::getInfo('baseClass', $segment->getOperationCarrierCode(), $segment->getFareCode()),
                        'fareBasis' => $segment->getFareCode(),
                        'fareName' => FareFamilies::getInfo('description', $segment->getOperationCarrierCode(), $segment->getFareCode()),
                        // TODO перенести заполнение FareFamilies на этап парсинга ответа от системы
                        'refundable'     => FareFamilies::getInfo('refundable', $segment->getOperationCarrierCode(), $segment->getFareCode()) === 'free' ? true : false,
                        'baggage'        => FareFamilies::getInfo('baggage', $segment->getOperationCarrierCode(), $segment->getFareCode()) === 'free' ? true : false,
                        'baggageMeasure' => $segment->getBaggageMeasure(),
                        'carryOn'        => FareFamilies::getInfo('carryOn', $segment->getOperationCarrierCode(), $segment->getFareCode()) === 'free' ? true : false,

                        'seats' => $segment->getAllowedSeatsBySubclass($segment->getSubClass()),
                        'info' => $info
                    ];
                }
            }

            // Прайсы
            $prices = $item->getPrices();
            $pricesData = [];
            if (is_array($prices)) {
                /*
                foreach ($prices as $typePassenger => $price) {
                    // Здесь объединять данные о тарифа для какждого типа пассажира
                }
                */

                $pricesData = [
                    'totalAmount' => $item->getTotalPrice()->getValue(),
                ];
            }

            $offerAttributes = [
                'system' => $item->getSystem(),
                'totalAmount' => $item->getTotalPrice()->getValue() . $item->getTotalPrice()->getCurrency(),
                'validationAirline' => $item->getValidatingCompany(),
                'segments' => $segmentsArr,
                'prices' => $pricesData
            ];

            if ($item->getRequisiteId()) {
                $offerAttributes['ruid'] = $item->getRequisiteId();
            }

            // TODO запихнуть методы преобразования в json в объекты RK
            $requestSegments = [];
            foreach ($request->getSegments() as $segment) {
                // Аэропорт/город отправления
                $origin = Airport::where('code', $segment->getDepartureCode())->with(['city', 'country'])->first();
                if (!$origin) {
                    $origin = City::where('code', $segment->getDepartureCode())->with(['country'])->first();
                }

                // Аэропорт/город прибытия
                $destination = Airport::where('code', $segment->getArrivalCode())->with(['city', 'country'])->first();
                if (!$destination) {
                    $destination = City::where('code', $segment->getArrivalCode())->with(['country'])->first();
                }

                $requestSegments[$segment->getWayNumber()][] = [
                    'origin'      => $origin,
                    'destination' => $destination,
                    'departureDate' => [
                        'date' => $segment->getDepartureDate()->getValue('Y-m-d'),
                        'time' => $segment->getDepartureDate()->getValue('H:i')
                    ],
                    'departureDateFormatted' => [
                        'date' => Carbon::createFromFormat('Y-m-d H:i', $segment->getDepartureDate()->getValue('Y-m-d H:i'))->formatLocalized('%a, %b. %d'),
                        'time' => Carbon::createFromFormat('Y-m-d H:i', $segment->getDepartureDate()->getValue('Y-m-d H:i'))->formatLocalized('%H:%M')
                    ],
                ];
            }

            $resultJSON['meta'] = [
                'request' => [
                    'hash'  => $request->getHash(),
                    'class' => $request->getClassType(),
                    'type'  => $request->getType(),
                    'adt'   => $request->getPassengerByType('ADT') ? $request->getPassengerByType('ADT')->getCount() : 0,
                    'chd'   => $request->getPassengerByType('CHD') ? $request->getPassengerByType('CHD')->getCount() : 0,
                    'inf'   => $request->getPassengerByType('INF') ? $request->getPassengerByType('INF')->getCount() : 0,
                    'isDirect' => $request->isDirect(),
                    'segments' => $requestSegments
                ]
            ];

            $resultJSON['data'][] = [
                'type' => 'offer',
                'id' => $itemKey,
                'attributes' => $offerAttributes
            ];
        }

        return $resultJSON;
    }

    /**
     * Перерабатывает результирующий массив с предложениями,
     * группируя тарифы по одинаковому рейсу и исключая не нужную информацию из сегментов
     *
     * @param $jsonResults
     * @return array
     */
    public static function groupOffersBy($jsonResults)
    {
        $groupResults = [];

        foreach ($jsonResults['data'] as $key1 => $offer1) {
            //
            $segmentsOffer1 = $offer1['attributes']['segments'];

            // Создание уникально идентификатора для перелета1
            $hashFlight1 = $offer1['attributes']['system'] . self::getUniqueHashBy(['flightNumber', 'departureDate', 'arrivalDate'], $segmentsOffer1);

            // Добавление информации о рейсе в итоговый массив
            if (!isset($groupResults[$hashFlight1])) {
                $groupResults[$hashFlight1] = $offer1;

                // Переделывание ниформации о тарифе ['attributes']['prices'] в группу тарифов
                $groupResults[$hashFlight1]['attributes']['prices'] = [
                    $offer1['attributes']['prices']
                ];

                // Перенос дополнительной информации в тариф из сегмента
                $groupResults[$hashFlight1]['attributes']['prices'][0] = array_merge([
                    'offerId'        => $offer1['id'],
                    'totalAmount'    => $offer1['attributes']['prices']['totalAmount']
                ], self::getServiceInfoFromSegments([
                    'fareBasis', 'classOfService', 'cabinClass', 'typeClass', 'fareName',
                    'refundable', 'baggage', 'baggageMeasure', 'carryOn'
                ], $segmentsOffer1));
            }

            // Сбор группы тарифов с таким же рейсами как и маршрута $offer1
            foreach ($jsonResults['data'] as $key2 => $offer2) {
                $segmentsOffer2 = $offer2['attributes']['segments'];

                // Создание уникально идентификатора для перелета2
                $hashFlight2 = $offer2['attributes']['system'] . self::getUniqueHashBy(['flightNumber', 'departureDate', 'arrivalDate'], $segmentsOffer2);

                // Если id предложений одинаковое, то это одно и тоже предложение
                if ($offer1['id'] === $offer2['id']) {
                    continue;
                }

                // Если количесво сегментов одинаково
                if ($hashFlight1 === $hashFlight2) {
                    $groupResults[$hashFlight1]['attributes']['prices'][] = $offer2['attributes']['prices'];

                    $pricesQuantity = count($groupResults[$hashFlight1]['attributes']['prices']);

                    // Перенос дополнительной информации в тариф из сегмента
                    $groupResults[$hashFlight1]['attributes']['prices'][$pricesQuantity - 1] = array_merge([
                        'offerId'        => $offer2['id'],
                        'totalAmount'    => $offer2['attributes']['prices']['totalAmount']
                    ], self::getServiceInfoFromSegments([
                        'fareBasis', 'classOfService', 'cabinClass', 'typeClass', 'fareName',
                        'refundable', 'baggage', 'baggageMeasure', 'carryOn'
                    ], $segmentsOffer2));
                }
            }

            // Исключение добавленных тарифов из общего списка не сгруппированных тарифов
            unset($jsonResults['data'][$key1]);
        }

        // Исключение ненужной информации
        $excludeSegmentKeys = [
            'fareBasis', 'classOfService', 'cabinClass', 'typeClass', 'fareName',
            'refundable', 'baggage', 'baggageMeasure', 'carryOn'
        ];
        foreach ($groupResults as $hashFlightKey => $groupFares) {
            //
            unset($groupResults[$hashFlightKey]['id']);
            unset($groupResults[$hashFlightKey]['attributes']['totalAmount']);

            // Удаление ключей из сегментов
            foreach ($groupFares['attributes']['segments'] as $segmentNum => $segment) {
                foreach ($segment as $segmentKey => $segmentValue) {

                    foreach ($excludeSegmentKeys as $excludeKey) {
                        if (isset($segment)) {
                            unset($groupResults[$hashFlightKey]['attributes']['segments'][$segmentNum][$excludeKey]);
                        }
                    }

                }
            }

        }

        // Сброс хешей-ключей
        $groupResults = array_values($groupResults);

        return [
            'meta' => $jsonResults['meta'],
            'data' => $groupResults
        ];
    }

    /**
     * TODO вынести в общий хелпер. Т.к. метод может использоваться универсально
     * TODO минус - не очевидная структура в массиве $resultArr
     *
     * Создание уникально идентификатора из значений указанных ключей
     *
     * @param array $keys Массив ключей значений, по которым будет составляться уникальный хеш
     * @param array $itemsArr Массив элементов
     * @param int $length Длина результирующего хеша
     * @return bool|string
     */
    public static function getUniqueHashBy(array $keys, array $itemsArr, $length = 10)
    {
        $resultArr = [];

        array_walk_recursive($itemsArr, function($item, $key, $checkingKeys) use (& $resultArr) {
            if (in_array($key, $checkingKeys)) {
                if (is_array($item) || is_object($item)) {
                    $resultArr[] = serialize($item);
                } else {
                    $resultArr[] = (string) $item;
                }
            }

        }, $keys);

        return substr(sha1(implode('', $resultArr)), 0, $length);
    }

    /**
     * Возвращает список сервисов со значениями по каждому сегменту
     *
     * @param array $serviceKeys Список ключей сервисов, которые будут выбраны из сегментов
     * @param array $allSegments Список сегментов в порядке следования
     * @return array
     */
    public static function getServiceInfoFromSegments(array $serviceKeys, array $allSegments)
    {
        $servicesList = [];

        array_walk_recursive($allSegments, function($item, $key, $checkingKeys) use (& $servicesList) {
            if (in_array($key, $checkingKeys)) {
                $servicesList[$key][] = (string) $item;
            }

        }, $serviceKeys);

        return $servicesList;
    }

}