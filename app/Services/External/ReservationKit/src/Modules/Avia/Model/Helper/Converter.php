<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;

use ReservationKit\src\Modules\Galileo\Model\Entity\SearchRequest as GalileoSearchRequest;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Price as S7AgentPrice;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment as S7AgentSegment;

class Converter
{
    /**
     * Преобразует массив параметров из поиска в поисковый объект
     *
     * Для 1-го этапа (поиска предложений)
     *
     * @param array $searchParams
     * @return \RK_Avia_Entity_Search_Request
     */
    public static function searchParamToRK(array $searchParams)
    {
        global $USER;

        // Параметры поискового запроса
        //$searchRequest = new \RK_Avia_Entity_Search_Request();
        $searchRequest = new GalileoSearchRequest();

        // IP пользователя
        $searchRequest->addOption('remote_ip', $USER['ip']);    //$_SERVER['REMOTE_ADDR']

        // Если класс не указан, то по-умолчанию берется Economy
        //$baseClass = empty($searchParams['class']) ? 'Y' : $searchParams['class'];
        $baseClass = $searchParams['class'];

        // Тип класса (Economy, Business, First)
        $searchRequest->setClassType(Request::getTypeClassByBase($baseClass));

        // Список авиакомпаний
        if (isset($searchParams['airlines'])) {
            $searchRequest->setCarriers($searchParams['airlines']);
        }

        // Заполнение сегментов
        foreach ($searchParams['city_out_code'] as $key => $cityCode) {
            $segment = new Segment();

            $departureDate = new \RK_Core_Date($searchParams['date_out_code'][0][$key], '!Ymd'); // !Ymd - означает, что время не учитывается

            // Время отправления с ..
            if (isset($searchParams['time_out_from'][$key])) {
                $dateTimeString = $searchParams['date_out_code'][0][$key] . $searchParams['time_out_from'][$key];
                $segment->setDepartureTimeRangeFrom(new \RK_Core_Date($dateTimeString, 'YmdHi'));
            }

            // Время отправления до ..
            if (isset($searchParams['time_out_to'][$key])) {
                $dateTimeString = $searchParams['date_out_code'][0][$key] . $searchParams['time_out_to'][$key];
                $segment->setDepartureTimeRangeTo(new \RK_Core_Date($dateTimeString, 'YmdHi'));
            }

            // Дата отправления
            $segment->setDepartureDate($departureDate->formatTo(\RK_Core_Date::DATE_FORMAT_SERVICES));
            // Аэропорт отправления
            $segment->setDepartureCode($searchParams['city_out_code'][$key]);
            // Аэропорт прибытия
            $segment->setArrivalCode($searchParams['city_in_code'][$key]);
            // Класс сегмента
            $segment->setBaseClass($baseClass);
            // Тип класса сегмента
            $segment->setTypeClass($searchRequest->getClassType());

            $searchRequest->addSegment($segment);
        }

        // Только прямые рейсы
        if (isset($searchParams['direct'])) $searchRequest->setDirect(true);

        // Заполнение пассажиров
        if ((int) $searchParams['ADT'] > 0) $searchRequest->addPassenger( new Passenger('ADT', $searchParams['ADT']) );
        if ((int) $searchParams['CHD'] > 0) $searchRequest->addPassenger( new Passenger('CHD', $searchParams['CHD']) );
        if ((int) $searchParams['INF'] > 0) $searchRequest->addPassenger( new Passenger('INF', $searchParams['INF']) );
        if ((int) $searchParams['INS'] > 0) $searchRequest->addPassenger( new Passenger('INS', $searchParams['INS']) );

        return $searchRequest;
    }

    /**
     * Для 2-го этапа (прайсинга)
     *
     * @param array $searchParams
     * @param Segment|null $segmentObj
     * @return \RK_Avia_Entity_Search_Request
     */
    public static function priceParamToRK(array $searchParams, Segment $segmentObj = null)
    {
        // Параметры поискового запроса
        $searchRequest = new \RK_Avia_Entity_Search_Request();

        // Данные о пассажирах
        $passengers = $searchParams['passengers'];

        // Заполнение сегментов
        foreach ($searchParams['segments'] as $numSegment => $segmentArr) {
            /* @var Segment|GalileoSegment|S7AgentSegment $segment */
            if ($segmentObj) {
                $segment = clone $segmentObj;
            } else {
                $segment = new Segment();
            }

            // Порядковый номер сегмента
            $segment->setId($numSegment);

            // Идентификатор сегмента
            $segment->setKey($segmentArr['key']);

            // Ключ-ссылка на тарифы
            $segment->setFareInfoRef($segmentArr['fareInfoRef']);

            // Номер плеча
            $segment->setWayNumber($segmentArr['section_number']);

            // Дата отправления
            $departureDate = new \RK_Core_Date($segmentArr['datetime_out'], 'Y-m-d H:i:s');
            if (!$departureDate->getDateTime() instanceof \DateTime) {
                $departureDate = new \RK_Core_Date($segmentArr['datetime_out'], 'YmdHis');
            }
            $segment->setDepartureDate($departureDate->formatTo(\RK_Core_Date::DATE_FORMAT_SERVICES));

            if ($segmentArr['timezone_out'] && $segment instanceof GalileoSegment) {
                $dateWithTimeZOne = new \DateTime($segment->getDepartureDate(), new \DateTimeZone($segmentArr['timezone_out']));

                $segment->setDepartureDate(new \RK_Core_Date($dateWithTimeZOne));
            }

            // Дата прибытия
            $arrivalDate = new \RK_Core_Date($segmentArr['datetime_in'], 'Y-m-d H:i:s');
            if (!$arrivalDate->getDateTime() instanceof \DateTime) {
                $arrivalDate = new \RK_Core_Date($segmentArr['datetime_in'], 'YmdHis');
            }
            $segment->setArrivalDate($arrivalDate->formatTo(\RK_Core_Date::DATE_FORMAT_SERVICES));

            if ($segmentArr['timezone_in'] && $segment instanceof GalileoSegment) {
                $dateWithTimeZOne = new \DateTime($segment->getArrivalDate(), new \DateTimeZone($segmentArr['timezone_in']));

                $segment->setArrivalDate(new \RK_Core_Date($dateWithTimeZOne));
            }

            // Аэропорт отправления
            $segment->setDepartureCode($segmentArr['airport_out']);
            // Аэропорт прибытия
            $segment->setArrivalCode($segmentArr['airport_in']);

            // Тип класса (Economy, Business, First)
            $segment->setTypeClass(Request::getTypeClassByBase($segmentArr['class']));

            // Класс сегмента
            $segment->setBaseClass($segmentArr['class']);

            // Подкласс сегмента. Берется у первого пассажира независимо от его типа.
            // Все типы пассажиров летят одним классом и подклассом в пределах сегмента
            if (isset($passengers[0]['BICS'])) {
                $segment->setSubClass($passengers[0]['BICS'][$numSegment]);
            }
            if (isset($passengers[0]['segments_info'])) {
                $segment->setSubClass($passengers[0]['segments_info'][$numSegment]['BIC']);
            }

            // Код тарифа
            if (isset($segmentArr['fare_code'])) {
                $segment->setFareCode($segmentArr['fare_code']);
            }

            // Номер рейса
            $segment->setFlightNumber($segmentArr['number']);

            // Маркетинговая компания
            $segment->setMarketingCarrierCode($segmentArr['airline']);

            // Компания перевозчик
            $segment->setOperationCarrierCode($segmentArr['airline_operating']);

            // Connection
            if (isset($segmentArr['connection']) && $segment instanceof GalileoSegment) {
                $segment->setNeedConnectionToNextSegment($segmentArr['connection']);
            }

            // Длительность перелета
            //$segment->set($segmentArr['number']);

            // Добавление сегмента в поисковый объект
            $searchRequest->addSegment($segment);
        }

        // Заполнение пассажиров
        $countPassengers = array();
        foreach ($passengers as $passenger) {
            $countPassengers[$passenger['type']] = empty($countPassengers[$passenger['type']]) ? 1 : ($countPassengers[$passenger['type']] + 1);
        }
        foreach ($countPassengers as $typePassenger => $count) {
            $searchRequest->addPassenger( new Passenger($typePassenger, $count));
        }

        return $searchRequest;
    }

    public static function getSegmentsInfoFromRk(array $segments)
    {
        $segments_info = array();
        //$segments_info[0] = array();

        // Сегменты
        /* @var Segment $segmentRK */
        foreach ($segments as $segmentNum => $segmentRK) {
            $journey = $segmentRK->getWayNumber();

            // segments info для блока ниже
            $segments_info[$journey][0][] = array(
                'class'    => $segmentRK->getBaseClass(),
                'BIC'      => $segmentRK->getSubClass(),
                'FIC'      => $segmentRK->getFareCode(),
                'baggage'  => $segmentRK->getBaggage() ?  $segmentRK->getBaggage() . 'PC' : '',
                //'services' => '',
                //'meals'    => ''
            );
        }

        return ;
    }

    /**
     * Экземпляр класса
     */
    protected static $_instance;

    private function __construct(){  }  // Защищаем от создания через new Singleton
    private function __clone()    {  }  // Защищаем от создания через клонирование
    private function __wakeup()   {  }  // Защищаем от создания через unserialize

    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new Converter());
    }

    public function toSiteFormat(array $results, $searchId = null, $isSet3DAgreement = false)
    {
        if (empty($results)) {
            return array();
        }

        //$availability = array();
        $convertResults = array();

        // Установка возможных классов
        /* @var \RK_Avia_Entity_Booking $offer */
        /*foreach ($results as $key => $offer) {
            /* @var Segment $segmentRK */
            /*$segmentsRK = $offer->getSegments();
            foreach ($segmentsRK as $segmentRK) {
                $availability = array_merge($availability, $segmentRK->getAllowedSeats());
            }
        }*/

        /* @var \RK_Avia_Entity_Booking $offer */
        foreach ($results as $key => $offer) {
            $passengers    = array();
            $segments      = array();
            $segments_info = array();

            $segmentsRK = $offer->getSegments();

            // Сегменты
            /* @var Segment|GalileoSegment $segmentRK */
            foreach ($segmentsRK as $segmentNum => $segmentRK) {
                $journey = $segmentRK->getWayNumber();

                if (!isset($segments[$journey])) {
                    /**
                     * Вложенный массив с ключом 0 - это вариант предложения по умолчанию, пока он только один
                     * На этапе группировки по стоимости появятся другие ключи
                     */
                    $segments[$journey] = array(array());
                }

                $segmentData = array();
                $segmentData['key']               = $segmentRK->getKey();
                $segmentData['fareInfoRef']       = $segmentRK->getFareInfoRef();
                $segmentData['number']            = $segmentRK->getFlightNumber();
                $segmentData['airline']           = $segmentRK->getMarketingCarrierCode();  // $offer->getValidatingCompany();
                $segmentData['airline_operating'] = $segmentRK->getOperationCarrierCode();
                $segmentData['airport_out']       = $segmentRK->getDepartureCode();
                $segmentData['airport_in']        = $segmentRK->getArrivalCode();
                $segmentData['datetime_out']      = $segmentRK->getDepartureDate()->formatTo(\RK_Core_Date::DATE_FORMAT_DB)->getValue();
                $segmentData['timezone_out']      = $segmentRK->getDepartureDate()->getValue('O');
                $segmentData['datetime_in']       = $segmentRK->getArrivalDate()->formatTo(\RK_Core_Date::DATE_FORMAT_DB)->getValue();
                $segmentData['timezone_in']       = $segmentRK->getArrivalDate()->getValue('O');
                $segmentData['flight_time']       = $segmentRK->getFlightTime();
                $segmentData['aircraft']          = ($segmentRK instanceof S7AgentSegment) ? '0' : $segmentRK->getAircraftCode();
                $segmentData['terminal_out']      = $segmentRK->getDepartureTerminal();
                $segmentData['terminal_in']       = $segmentRK->getArrivalTerminal();
                $segmentData['availability']      = $segmentRK->getAllowedSeats();
                $segmentData['section_number']    = $segmentRK->getWayNumber();
                $segmentData['fare_code']         = $segmentRK->getFareCode();
                $segmentData['connection']        = 0;
                //$segmentData['is_complex']        = false;
                $segmentData['baggage'] = '';

                foreach ($offer->getPrices() as $price) {
                    if ($price instanceof S7AgentPrice) {
                        $segmentData['baggage'] = $segmentData['baggage'] . $price->getType() . ':' . $price->getBaggageAllowanceBySegment($segmentNum)->getKey() . ':' . $price->getBaggageAllowanceBySegment($segmentNum)->getBaggageValue() . ',';
                    }
                }
                $segmentData['baggage'] = trim($segmentData['baggage'], ',');

                // Используется для галилео
                if ($segmentRK instanceof GalileoSegment) {
                    $segmentData['connection'] = $segmentRK->isNeedConnectionToNextSegment(); //$availability;
                }

                $segments[$journey][0][] = $segmentData;

                // segments_info для блока ниже
                $segments_info[$journey][0][] = array(
                    'class'   => $segmentRK->getBaseClass(),
                    'BIC'     => $segmentRK->getSubClass(),
                    'FIC'     => $segmentRK->getFareCode(),
                    'baggage' => $segmentRK->getBaggage(),
                    //'services' => '',
                    //'meals'    => ''
                );
            }

            // Пассажиры
            $passengerNum = 0;

            $baggageAssoc = array();

            /* @var \RK_Avia_Entity_Price|S7AgentPrice $price */
            foreach ($offer->getPrices() as $price) {
                if ($price instanceof S7AgentPrice) {
                    // Багаж
                    $baggageAssoc[$price->getType()] = $price->getBaggageAllowance();

                    // Абсолютный номер сегмента (не относительно плеча)
                    $numSegmentAbs = 0;

                    foreach ($segments_info as $numWay => $way) {
                        foreach ($way[0] as $numSegment => $segment) {

                            // Коректировка данных о багаже для типа пассажира
                            $segments_info[$numWay][0][$numSegment]['baggage'] = $price->getBaggageAllowanceBySegment($numSegmentAbs);

                            $numSegmentAbs++;
                        }
                    }
                }

                $passengers[$passengerNum] = array();
                for ($i = 0; $i < $price->getQuantity(); $i++) {
                    // Тип пассажира
                    $passengers[$passengerNum]['type'] = $price->getType();

                    // segments info
                    $passengers[$passengerNum]['segments_info'] = $segments_info;

                    // Прайсы
                    if ($price->getEquivFare()) {
                        $obligations = array();
                        $obligations[0]['type'] = 'tariff';
                        $obligations[0]['amount'] = number_format($price->getEquivFare()->getValue(), 2, '.', '');
                        $obligations[0]['currency'] = $price->getEquivFare()->getCurrency();
                    } else if ($price->getBaseFare()) {
                        $obligations = array();
                        $obligations[0]['type'] = 'tariff';
                        $obligations[0]['amount'] = number_format($price->getBaseFare()->getValue(), 2, '.', '');
                        $obligations[0]['currency'] = $price->getBaseFare()->getCurrency();
                    }

                    $obligations[1]['type'] = 'tax';
                    $obligations[1]['amount'] = number_format($price->getTaxesSum()->getValue(), 2, '.', '');
                    $obligations[1]['currency'] = $price->getTaxesSum()->getCurrency();

                    $passengers[$passengerNum]['quotes'] = array(
                        array(
                            'obligations' => array(),
                            'owner' => $offer->getValidatingCompany()
                        )
                    );

                    $passengers[$passengerNum]['quotes'][0]['obligations']   = $obligations;
                    $passengers[$passengerNum]['quotes'][0]['owner']         = $offer->getValidatingCompany();  // $offer->getSegment(0)->getOperationCarrierCode()
                    $passengers[$passengerNum]['quotes'][0]['is_returnable'] = $price->isRefundable();

                    $passengerNum++;
                }
            }

            $convertResults[$key]['passengers']       = $passengers;
            $convertResults[$key]['segments']         =  $segments;
            $convertResults[$key]['search_id']        = $searchId;
            $convertResults[$key]['baggage_assoc']    = serialize($baggageAssoc);
            $convertResults[$key]['isSet3DAgreement'] = $isSet3DAgreement;
        }

        return $convertResults;
    }

    // TODO есть блоки как метода toSiteFormat
    public function availabilityToSiteFormat(array $results, $classType)
    {
        foreach ($results as $numWayList => $way) {
            foreach ($way as $numVariant => $variant) {
                foreach ($variant as $numSegment => $segmentRK) {
                    /** @var GalileoSegment $segmentRK */
                    $journey = $segmentRK->getWayNumber();

                    if (!isset($segments[$journey])) {
                        /**
                         * Вложенный массив с ключом 0 - это вариант предложения по умолчанию, пока он только один
                         * На этапе группировки по стоимости появятся другие ключи
                         */
                        $segments[$journey] = array(array());
                    }

                    // Список доступных классов
                    $availabilityClasses = 'NNN';
                    $availabilityList = array();
                    $airAvailInfo = $segmentRK->getAirAvailInfo();
                    if (is_array($airAvailInfo)) {
                        // Список свободных мест у класса $classType
                        foreach ($airAvailInfo as $bookingCodeInfo) {
                            if ($bookingCodeInfo->getCabinClass() === $classType) {
                                $bookingCounts = explode('|', $bookingCodeInfo->getBookingCounts());
                                foreach ($bookingCounts as $availability) {
									if (!isNumber($availability[1])) $availability[1] = $availability[1] == 'A' ? 9 : 0;
                                    $availabilityList[$availability[0]] = $availability[1];
                                }

                                break;
                            }
                        }
						


                        foreach ($airAvailInfo as $bookingCodeInfo) {
                            $isAvailSeats = false;
                            
                            $bookingCounts = explode('|', $bookingCodeInfo->getBookingCounts());
							//foreach ($bookingCounts as $key=>$val) if (!isNumber($val)) $bookingCounts[$key] = $val == 'A' ? 9 : 0;
                            foreach ($bookingCounts as $availability) {
								if (!isNumber($availability[1])) $availability[1] = $availability[1] == 'A' ? 9 : 0;
                                // Если хотя бы НА один подкласс есть хотя бы одно место, то сегмент доступен для выбора
                                if ((int) $availability[1] >= 1 && ! $isAvailSeats) {
                                    $isAvailSeats = true;
                                }
                            }

                            if ($bookingCodeInfo->getCabinClass() === 'First' && $isAvailSeats) {
                                $availabilityClasses[2] = 'Y';
                            }

                            if ($bookingCodeInfo->getCabinClass() === 'Business' && $isAvailSeats) {
                                $availabilityClasses[1] = 'Y';
                            }

                            if ($bookingCodeInfo->getCabinClass() === 'PremiumEconomy' && $isAvailSeats) {
                                $availabilityClasses[1] = 'Y';
                            }

                            if ($bookingCodeInfo->getCabinClass() === 'Economy' && $isAvailSeats) {
                                $availabilityClasses[0] = 'Y';
                            }
                        }
                    }
					

					
                    $segmentData = array();
                    $segmentData['key']               = $segmentRK->getKey();
                    //$segmentData['fareInfoRef']       = $segmentRK->getFareInfoRef();
                    $segmentData['number']            = $segmentRK->getFlightNumber();
                    $segmentData['airline']           = $segmentRK->getMarketingCarrierCode();  // $offer->getValidatingCompany();
                    $segmentData['airline_operating'] = $segmentRK->getOperationCarrierCode();
                    $segmentData['airport_out']       = $segmentRK->getDepartureCode();
                    $segmentData['airport_in']        = $segmentRK->getArrivalCode();
                    $segmentData['datetime_out']      = $segmentRK->getDepartureDate()->formatTo(\RK_Core_Date::DATE_FORMAT_DB)->getValue();
                    $segmentData['timezone_out']      = $segmentRK->getDepartureDate()->getValue('O');
                    $segmentData['datetime_in']       = $segmentRK->getArrivalDate()->formatTo(\RK_Core_Date::DATE_FORMAT_DB)->getValue();
                    $segmentData['timezone_in']       = $segmentRK->getArrivalDate()->getValue('O');
                    $segmentData['flight_time']       = $segmentRK->getFlightTime();
                    $segmentData['aircraft']          = $segmentRK->getAircraftCode();
                    $segmentData['terminal_out']      = $segmentRK->getDepartureTerminal();
                    $segmentData['terminal_in']       = $segmentRK->getArrivalTerminal();
                    $segmentData['availability']      = $availabilityList;
                    $segmentData['connection']        = null;
                    $segmentData['section_number']    = $numWayList;
                    $segmentData['classes']           = $availabilityClasses;
                    //$segmentData['is_complex']        = false;

                    // Используется для галилео
                    if ($segmentRK instanceof GalileoSegment) {
                        $segmentData['connection'] = $segmentRK->isNeedConnectionToNextSegment(); //$availability;
                    }

                    $resultSegments[$numWayList][$numVariant][$numSegment] = $segmentData;
                }
            }
        }
					
        return $resultSegments;
    }

    /**
     * Группировка предложений по стоимости
     *
     * @param $offers
     * @return array
     */
    public function groupOfferByAmount2($offers)
    {
        $results = array();
        $segments = array();

        if (isset($offers)) {
            // Сравниваем предложения от sabre
            foreach ($offers as $key => $offer) {
                $priceOffer = $this->getTotalPrice($offer['passengers']);

                if (!isset($results[$priceOffer])) {
                    $results[$priceOffer] = $offer;
                    continue;

                } else {
                    // Объединяем сегменты в плечах у сравниваемых предложений
                    $segments = $this->mergeJourney($results[$priceOffer]['segments'], $offer['segments']);

                    // Объединение segments_info в данных о пассажирах блять
                    $passengers = $this->mergePassengers($results[$priceOffer]['passengers'], $offer['passengers']);

                    // Удаляем одинаковые варианты перелетов и информацию в segments_info
                    list($segments, $passengers) = $this->_excludeEqualSegments($segments, $passengers);

                    $results[$priceOffer]['segments'] = $segments;
                    $results[$priceOffer]['passengers'] = $passengers;
                }
            }

            if (isset($results)) {
                // Сортировка по цене
                ksort($results);

                // Удаление стоимости предложения из ключа
                $results = array_values($results);
            }
        }

        return $results;
    }

    public function groupOfferByPCC($offers)
    {
        $results = array();

        if (isset($offers)) {
            // Группировка по PCC
            /* @var \RK_Avia_Entity_Booking $offer */
            foreach ($offers as $offer) {
                $results[$offer->getRequisiteRules()->getSearchPCC()][] = $offer;
            }

            // Тупой, но понятный фильтр одинаковых предложений

            /* @var \RK_Avia_Entity_Booking $offer36WB */
            /* @var \RK_Avia_Entity_Booking $offer6UQ2 */
            /* @var \RK_Avia_Entity_Booking $offerL8W */
            /* @var \RK_Avia_Entity_Booking $offer33VU */
            /* @var \RK_Avia_Entity_Booking $offer80UE */

            // Предложения из '36WB' заменяют такие же предложения в '6UQ2', 'L8W' и '33VU'
            if (isset($results['36WB'])) {
                foreach ($results['36WB'] as $offer36WB) {

                    if (isset($results['6UQ2'])) {
                        foreach ($results['6UQ2'] as $key6UQ2 => $offer6UQ2) {
                            if ($offer36WB->isEqualOffer($offer6UQ2)) {
                                unset($results['6UQ2'][$key6UQ2]);
                            }
                        }
                    }

                    if (isset($results['L8W'])) {
                        foreach ($results['L8W'] as $keyL8W => $offerL8W) {
                            if ($offer36WB->isEqualOffer($offerL8W)) {
                                unset($results['L8W'][$keyL8W]);
                            }
                        }
                    }

                    if (isset($results['33VU'])) {
                        foreach ($results['33VU'] as $key33VU => $offer33VU) {
                            if ($offer36WB->isEqualOffer($offer33VU)) {
                                unset($results['33VU'][$key33VU]);
                            }
                        }
                    }

                    if (isset($results['80UE'])) {
                        foreach ($results['80UE'] as $key80UE => $offer80UE) {
                            if ($offer36WB->isEqualOffer($offer80UE)) {
                                unset($results['80UE'][$key80UE]);
                            }
                        }
                    }
                }
            }

            // Предложения из '6UQ2' заменяют такие же предложения в '80UE'
            if (isset($results['6UQ2'])) {
                foreach ($results['6UQ2'] as $offer6UQ2)
					if ($results['80UE'])
                    foreach ($results['80UE'] as $key80UE => $offer80UE) {
                        if ($offer6UQ2->isEqualOffer($offer80UE)) unset($results['80UE'][$key80UE]);
                }
            }

            // Предложения из 'L8W' заменяют такие же предложения в '80UE'
            if (isset($results['L8W'])) {
                foreach ($results['L8W'] as $offerL8W) {
                    foreach ($results['80UE'] as $key80UE => $offer80UE) {
                        if ($offerL8W->isEqualOffer($offer80UE)) {
                            unset($results['80UE'][$key80UE]);
                        }
                    }
                }
            }

            /*
            // Предложения из '80UE' заменяют такие же предложения в '33VU'
            if (isset($results['80UE'])) {
                foreach ($results['80UE'] as $offer80UE) {
                    foreach ($results['33VU'] as $key33VU => $offer33VU) {
                        if ($offer80UE->isEqualOffer($offer33VU)) {
                            unset($results['33VU'][$key33VU]);
                        }
                    }
                }
            }
            */
        }

        return $results;
    }

    /**
     * Группировка предложений по стоимости
     *
     * @param $offers
     * @return array
     */
    public function groupOfferByAmount($offers)
    {
        $results = array();
        $segments = array();

        if (isset($offers)) {
            // Сравниваем предложения от sabre
            foreach ($offers as $key => $offer) {
                $priceOffer = $this->getTotalPrice($offer['passengers']);

                if (!isset($results[$priceOffer])) {
                    $results[$priceOffer] = $offers[$key];
                }

                foreach ($offers as $key2 => $offer2) {
                    // Попытка сравнить предложение с самим собой
                    if ($key === $key2) {
                        continue;
                    }

                    // Если стоимости предложений равны, то объединяем предложения
                    $priceOffer2 = $this->getTotalPrice($offer2['passengers']);     // TODO сравнение тарифов пожно сделать по аналогии со сравнение маршрута ниже
                    if ($priceOffer === $priceOffer2 /*&& $isEqualRoute */) {
                        // Объединяем сегменты в плечах у сравниваемых предложений
                        $segments  = $this->mergeJourney($results[$priceOffer]['segments'], $offer2['segments']);
                        // Объединение segments_info в данных о пассажирах блять
                        $passengers = $this->mergePassengers($results[$priceOffer]['passengers'], $offer2['passengers']);
                    } else {
                        $segments  = $results[$priceOffer]['segments'];
                        $passengers = $results[$priceOffer]['passengers'];
                    }

                    // Удаляем одинаковые варианты перелетов и информацию в segments_info
                    list($segments, $passengers) = $this->_excludeEqualSegments($segments, $passengers);

                    $results[$priceOffer]['segments'] = $segments;
                    $results[$priceOffer]['passengers'] = $passengers;

                }
                unset($offers[$key]);
            }

            if (isset($results)) {
                // Сортировка по цене
                ksort($results);
                // Удаление стоимости предложения из ключа
                $results = array_values($results);
            }
        }

        return $results;
    }

    public function getTotalPrice($passengers)
    {
        $totalPrice = 0;
        foreach ($passengers as $passenger) {
            foreach ($passenger['quotes'][0]['obligations'] as $amount) {
                $totalPrice = $totalPrice + (float) $amount['amount'];
            }
        }
        return $totalPrice;
    }

    /**
     * Объединение вариантов в плечах предложений
     *
     * Количество плечей в предложении должно быть одинаковым
     *
     * @param $offer1
     * @param $offer2
     * @return array
     */
    public function mergeJourney($offer1, $offer2)
    {
        // Проверка количества плечей
        if (count($offer1) !== count($offer2)) {
            return false;
        }

        $segments = array();
        foreach ($offer1 as $keyJourney1 => $journey1) {
            if (!isset($segments[$keyJourney1])) {
                $segments[$keyJourney1] = array();
            }

            $segments[$keyJourney1] = array_merge($segments[$keyJourney1], $journey1);

            foreach ($offer2 as $keyJourney2 => $journey2) {
                if ($keyJourney1 === $keyJourney2) {
                    $segments[$keyJourney1] = array_merge($segments[$keyJourney1], $journey2);
                }
            }
        }

        return $segments;
    }

    /**
     * Объединение данных в пассажирах
     *
     * Объединяются данные из segments_info, т.к. в остальных ключах данные должны совпадать.
     * Объединение происходит за счет добавления в $passengers1 segments_info из $passengers2.
     * ВНИМАНИЕ: типы пассажиров должны идти в одинаковой последовательности, иначе возможна ошибка.
     *
     * @param array $passengers1
     * @param array $passengers2
     * @return array
     * @throws Exception
     */
    public function mergePassengers(array $passengers1, array $passengers2)
    {
        // Проверка количества пассажиров
        if (count($passengers1) !== count($passengers2)) {
            return false;
        }

        $passengers = array();
        foreach ($passengers1 as $keyPassenger1 => $passenger1) {
            foreach ($passengers2 as $keyPassenger2 => $passenger2) {
                // Дополнительная проверка на тип пассажира
                if ($keyPassenger1 === $keyPassenger2 && $passenger1['type'] === $passenger2['type']) {
                    $passengers[$keyPassenger1] = $passenger1;
                    $passengers[$keyPassenger1]['segments_info'] = $this->mergeSegmentsInfo($passenger1['segments_info'], $passenger2['segments_info']);
                }
            }
        }

        // На выходе количество пассажиров должно быть равно количеству пассажиров во входных параметрах
        if (count($passengers) !== count($passengers1)) {
            // Ошибка
            throw new \RK_Gabriel_Exception('Fail count merge passengers');
        }

        return $passengers;
    }

    public function mergeSegmentsInfo(array $segmentsInfo1, array $segmentsInfo2)
    {
        // Проверка количества плечей
        if (count($segmentsInfo1) !== count($segmentsInfo1)) {
            return false;
        }

        $segmentsInfo = array();
        foreach ($segmentsInfo1 as $journeyNum1 => $journey1) {
            if (!isset($passengers[$journeyNum1])) {
                $segmentsInfo[$journeyNum1]['segments_info'] = array();
            }

            $segmentsInfo[$journeyNum1] = array_merge($segmentsInfo[$journeyNum1], $journey1);

            foreach ($segmentsInfo2 as $journeyNum2 => $journey2) {
                if ($journeyNum1 === $journeyNum2) {
                    $segmentsInfo[$journeyNum1] = array_merge($segmentsInfo[$journeyNum1], $journey2);
                }
            }
        }

        return $segmentsInfo;
    }

    /**
     * Исключает из массива сегментов одинкаковые сегменты
     *
     * Также обновляются данные segments_info в пассажирах
     *
     * @param $segments
     * @return array
     */
    private function _excludeEqualSegments($segments, $passengers)
    {
        $timesRoute = array();
        $distinctSegments = array();
        $distinctPassengers = array();

        foreach ($segments as $keyJourney => $journey) {
            foreach ($journey as $variantNum => $variant) {
                $routeTime = $this->getTimeRoute($variant);

                if (!in_array($routeTime, $timesRoute)) {
                    $timesRoute[] = $routeTime;
                    $distinctSegments[$keyJourney][] = $variant;

                    // Segments_Info в пассажирах заполяется заново
                    foreach ($passengers as $passengerNum => $passenger) {
                        $variantSegmentsInfo = $passenger['segments_info'][$keyJourney][$variantNum];

                        $distinctPassengers[$passengerNum]['type'] = $passenger['type'];
                        $distinctPassengers[$passengerNum]['segments_info'][$keyJourney][] = $variantSegmentsInfo;
                        $distinctPassengers[$passengerNum]['quotes'] = $passenger['quotes'];
                    }
                }
            }
        }

        return array($distinctSegments, $distinctPassengers);
    }

    /**
     * Строит маршрут времени
     *
     * @param $variant
     * @return array|string
     */
    function getTimeRoute($variant)
    {
        $time = array();
        foreach ($variant as $segment) {
            $time[] = $segment['datetime_out'] . ' -> ' . $segment['datetime_in'];
        }
        $time = implode(' ', $time);
        return $time;
    }
}