<?php

namespace ReservationKit\src\Modules\Sirena\Model\ResponseParser;

use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\FareInfo;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\BaggageAllowance as S7BaggageAllowance;
use ReservationKit\src\Modules\Sirena\Model\Entity\Booking as SirenaBooking;
use ReservationKit\src\Modules\Sirena\Model\Entity\Segment as SirenaSegment;
use ReservationKit\src\Modules\Sirena\Model\Entity\Price   as SirenaPrice;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\FareInfo as S7AgentFareInfo;

use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Requisites;

use ReservationKit\src\Modules\Sirena\Model\Helper\RequestHelper;

use ReservationKit\src\Modules\Avia\Model\Exception\PassengerPriceNotSetException;

class PricingParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->answer->pricing)) {
            $body = $this->getResponse()->answer->pricing;
        } else {
            throw new Exception('Bad ' . __CLASS__ . ' response content');
        }

        // Разбор параметров
        $results = array();

        // Варианты
        foreach ($body->variant as $Variant) {
            $booking = new SirenaBooking();

            // Сегменты
            $FlightSegmentList = array();
            foreach ($Variant->flight as $flight) {
                $segment = new SirenaSegment();

                // Отправление
                $segment->setDepartureCode((string) $flight->origin);
                $segment->setDepartureDate(new \RK_Core_Date((string) $flight->deptdate . ' ' . (string) $flight->depttime, \RK_Core_Date::DATE_FORMAT_RUS_SHORT_NO_SEC));

                $departureTerminalName = isset($flight->origin['terminal']) ? (string) $flight->origin['terminal']: null;
                $segment->setDepartureTerminal($departureTerminalName);

                // Прибытие
                $segment->setArrivalCode((string) $flight->destination);
                $segment->setArrivalDate(new \RK_Core_Date((string) $flight->arrvdate . ' ' . (string) $flight->arrvtime, \RK_Core_Date::DATE_FORMAT_RUS_SHORT_NO_SEC));

                $arrivalTerminalName = isset($flight->destination['terminal']) ? (string) $flight->destination['terminal']: null;
                $segment->setArrivalTerminal($arrivalTerminalName);

                // Оперирующая компания
                $segment->setOperationCarrierCode((string) $flight->company);

                // Маркетинговая компания
                $segment->setMarketingCarrierCode((string) $flight->price['validating_company']);

                // Номер рейса
                $segment->setFlightNumber((string) $flight->num);

                // Время перелета
                $segment->setFlightTime((string) $flight->flightTime);

                // Дальность перелета
                //$segment->setFlightDistance((string) $flight->);

                // Номер борта
                $segment->setAircraftCode((string) $flight->airplane);

                //$segment->addAllowedSeat((string) $FlightSegment->ClassOfService->Code, (string) $FlightSegment->ClassOfService->Code['SeatsLeft']);

                // Код тарифа
                //$segment->setFareCode($FareCodesList[(string) $FlightSegment['SegmentKey']]);    //$segment->setBaseFare($fareInfo->getFareCode());

                // Базовый класс сегмента
                $segment->setBaseClass((string) $flight->subclass['baseclass']);

                // Подкласс сегмента
                $segment->setSubClass((string) $flight->subclass);

                // Тип класса сегмента
                $segment->setTypeClass(RequestHelper::getTypeClassByBase($segment->getBaseClass()));

                // Добавление сегмента
                $booking->addSegment($segment);

                // Определение количества типов пассажиров
                $passengerTypeQuantity = array();
                foreach ($flight->price as $price) {
                    $passengerTypeQuantity[(string) $price['code']] = isset($passengerTypeQuantity[(string) $price['code']]) ? $passengerTypeQuantity[(string) $price['code']] + 1 : (int) $price['count'];
                }

                // Прайсы
                foreach ($flight->price as $price) {
                    // Валюта прайсов
                    $currencyPrice = (string) $price['currency'];

                    // Стоимость тарифа
                    $fareAmount = new \RK_Core_Money((string) $price->fare, $currencyPrice);

                    // Таксы
                    $totalTaxesAmount = new \RK_Core_Money(0.0, $currencyPrice);
                    foreach ($price->tax as $tax) {
                        $totalTaxesAmount = $totalTaxesAmount->add(new \RK_Core_Money((float) $tax, $currencyPrice));
                    }

                    // Тип пассажира
                    $typePassenger = str_replace('CNN', 'CHD', (string) $price['orig_code']);

                    try {
                        $farePrice = $booking->getPriceByTypePassenger($typePassenger, false);

                        if ($farePrice && $farePrice->getType() === $typePassenger) {
                            $farePrice->setBaseFare( $farePrice->getBaseFare()->add($fareAmount) );
                            $farePrice->setTotalFare( $farePrice->getTotalFare()->add($fareAmount)->add($totalTaxesAmount) );  // Прибавление к базовому тарифу суммы такс, затем умножение на количество поссажиров данного (ADT, CHD или INF) типа

                        } else {
                            $farePrice = new SirenaPrice();

                            // Тип пассажира
                            $farePrice->setType($typePassenger);

                            // Багаж TODO сделать класс багада для Сирены
                            if (isset($price['baggage'])) {
                                $BaggageAllowance = new S7BaggageAllowance();
                                $BaggageAllowance->setBaggageValue((string) $price['baggage']);

                                $farePrice->addBaggageAllowance($BaggageAllowance);
                            }

                            // Количество пассажиров данного типа
                            $farePrice->setQuantity($passengerTypeQuantity[$typePassenger]);

                            $farePrice->setBaseFare($fareAmount);
                            $farePrice->setTotalFare($fareAmount->add($totalTaxesAmount));

                            // Добавление прайсов
                            $booking->addPrice($farePrice->getType(), $farePrice);
                        }

                        // Дозаполнение такс
                        foreach ($price->tax as $tax) {
                            $farePrice->addTax((string) $tax['code'], new \RK_Core_Money((string) $tax, $currencyPrice));
                        }

                        // Валидирующая компания
                        $booking->setValidatingCompany((string) $price['validating_company']);

                    } catch (PassengerPriceNotSetException $e) {

                    } catch (\Exception $e) {
                        // Различия в валюте при операции сложения
                    }
                }
            }

            // Таймлимит
            //$booking->setTimelimit(new \RK_Core_Date((string) $AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_SERVICES));

            $results[] = $booking;
        }

        $this->setResult($results);

        return $results;
    }
}