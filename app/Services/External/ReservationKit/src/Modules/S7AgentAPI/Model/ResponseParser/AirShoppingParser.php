<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\FareInfo;
use ReservationKit\src\Modules\Avia\Model\Exception\PassengerPriceNotSetException;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\BaggageAllowance;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Booking as S7AgentBooking;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment as S7AgentSegment;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Price as S7AgentPrice;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\FareInfo as S7AgentFareInfo;

use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

class AirShoppingParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->AirShoppingRS->Success)) {
            $body = $this->getResponse()->Body->AirShoppingRS;
        } else {
            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }

        // Разбор параметров
        $results = array();

        // Список типов пассажиров для поиска и их количество
        $AnonymousTravelerList = array();
        foreach ($body->DataLists->AnonymousTravelerList->AnonymousTraveler as $AnonymousTraveler) {
            $typePassenger = (string) $AnonymousTraveler->PTC;
            $countPassenger = (string) $AnonymousTraveler->PTC['Quantity'];

            if ($countPassenger > 0) {
                $AnonymousTravelerList[(string) $AnonymousTraveler['ObjectKey']] = new Passenger($typePassenger, $countPassenger);
            }
        }

        // Список соответствия ключей тарифных групп ключам сегментов
        /*
        $FareCodesList = array();
        foreach ($body->DataLists->FareList->FareGroup as $FareGroup) {
            $FareCodesList[(string) $FareGroup['refs']] = (string) $FareGroup->FareBasisCode->Code;
        }
        */

        // Список описания тарифов
        /*
        foreach ($body->DataLists->PriceClassList->PriceClass  as $PriceClass) {
            $fareInfo = new S7AgentFareInfo();
            $fareInfo->setBaggageAllowance();
            $fareInfo->setFareCode((string) $PriceClass->FareBasisCode);
        }
        */

        // Список соответствия ключей перелета ключам плечей
        /*
        $i = 0;
        $SegmentsReferencesList = array();
        $OriginDestinationList  = array();
        foreach ($body->DataLists->OriginDestinationList->OriginDestination as $OriginDestination) {
            $FlightReferences     = (string) $OriginDestination->FlightReferences;
            $FlightReferencesList = explode(' ', $FlightReferences);

            $SegmentsReferencesList = array_merge($SegmentsReferencesList, array_fill_keys($FlightReferencesList, (string) $OriginDestination['OriginDestinationKey']));

            // Список соответствия ключей плейчей их индексам
            $OriginDestinationList[(string) $OriginDestination['OriginDestinationKey']] = $i++;
        }
        */

        // Список соответствия ключей перелетов (плечей) ключам сегментов
        /*
        $FlightList = array();
        foreach ($body->DataLists->FlightList->Flight as $Flight) {
            $segments = explode(' ', (string) $Flight->SegmentReferences);
            $FlightList[(string) $Flight['FlightKey']] = $segments;
        }
        */

        // Список параметров багажа
        $baggageAllowanceList = array();
        foreach ($body->DataLists->CheckedBagAllowanceList->CheckedBagAllowance as $CheckedBagAllowance) {
            if (!isset($baggageAllowanceList[(string) $CheckedBagAllowance['ListKey']])) {
                $baggageAllowanceList[(string) $CheckedBagAllowance['ListKey']] = [];
            }

            $baggageAllowance = new BaggageAllowance();
            $baggageAllowance->setKey((string) $CheckedBagAllowance['ListKey']);

            /*
            if (isset($CheckedBagAllowance->PieceAllowance)) {
                $baggageAllowance->setBaggageValue((string) $CheckedBagAllowance->PieceAllowance->Descriptions->Description->Text);
            }
            */

            if (isset($CheckedBagAllowance->AllowanceDescription)) {
                $baggageAllowance->setBaggageValue((string) $CheckedBagAllowance->AllowanceDescription->ApplicableBag);
            }

            $baggageAllowanceList[$baggageAllowance->getKey()] = $baggageAllowance;
        }

        // Список сегментов
        $FlightSegmentList = array();
        foreach ($body->DataLists->FlightSegmentList->FlightSegment as $FlightSegment) {
            $segment = new S7AgentSegment();

            // Отправление
            $FlightSegmentDeparture = $FlightSegment->Departure;
            $segment->setDepartureCode((string) $FlightSegmentDeparture->AirportCode);
            $segment->setDepartureDate(new \RK_Core_Date((string) $FlightSegmentDeparture->Date . ' ' . (string) $FlightSegmentDeparture->Time, \RK_Core_Date::DATE_FORMAT_NO_SEC));

            $departureTerminalName = isset($FlightSegmentDeparture->Terminal, $FlightSegmentDeparture->Terminal->Name) ? (string) $FlightSegmentDeparture->Terminal->Name: null;
            $segment->setDepartureTerminal($departureTerminalName);

            // Прибытие
            $FlightSegmentArrival = $FlightSegment->Arrival;
            $segment->setArrivalCode((string) $FlightSegmentArrival->AirportCode);
            $segment->setArrivalDate(new \RK_Core_Date((string) $FlightSegmentArrival->Date . ' ' . (string) $FlightSegmentArrival->Time, \RK_Core_Date::DATE_FORMAT_NO_SEC));

            $arrivalTerminalName = isset($FlightSegmentArrival->Terminal, $FlightSegmentArrival->Terminal->Name) ? (string) $FlightSegmentArrival->Terminal->Name: null;
            $segment->setArrivalTerminal($arrivalTerminalName);

            // Оперирующая компания
            $segment->setOperationCarrierCode((string) $FlightSegment->OperatingCarrier->AirlineID);

            // Маркетинговая компания
            $segment->setMarketingCarrierCode((string) $FlightSegment->MarketingCarrier->AirlineID);

            // Номер рейса
            $segment->setFlightNumber((string) $FlightSegment->MarketingCarrier->FlightNumber);

            // Время перелета FIXME уродское вычисление. Надо упростить
            $flightTime = (string) $FlightSegment->FlightDetail->FlightDuration->Value;
            $intervalInSeconds = (new \DateTime())->setTimeStamp(0)->add(new \DateInterval($flightTime))->getTimeStamp();
            $intervalInMinutes = $intervalInSeconds / 60;

            $segment->setFlightTime($intervalInMinutes);

            // Дальность перелета
            $segment->setFlightDistance((string) $FlightSegment->FlightDetail->FlightDistance->Value);

            // Номер борта TODO переделать название метода на setAircraftEquipment со всеми вытекающими последствиями
            $segment->setAircraftCode(str_replace(' ', '', ((string) $FlightSegment->Equipment->AirlineEquipCode)));

            //$segment->addAllowedSeat((string) $FlightSegment->ClassOfService->Code, (string) $FlightSegment->ClassOfService->Code['SeatsLeft']);

            // Код тарифа
            //$segment->setFareCode($FareCodesList[(string) $FlightSegment['SegmentKey']]);    //$segment->setBaseFare($fareInfo->getFareCode());

            // Тип класса сегмента
            //$segment->setTypeClass((string) $BookingInfo['CabinClass']);

            // Базовый класс сегмента
            //$segment->setBaseClass(Request::getBaseClassByType($segment->getTypeClass()));

            // Подкласс сегмента
            //$segment->setSubClass((string) $BookingInfo['BookingCode']);

            $FlightSegmentList[(string) $FlightSegment['SegmentKey']] = $segment;
        }

        // Список тарифов
        if (isset($body->OffersGroup, $body->OffersGroup->AirlineOffers)) {
            // Все предложения
            foreach ($body->OffersGroup->AirlineOffers as $AirlineOffersGroup) {
                // Flex и Basic
                foreach ($AirlineOffersGroup->AirlineOffer as $AirlineOffer) {
                    $booking = new S7AgentBooking();

                    //
                    $baggageAssoc = array();

                    $segmentBaggage = array();

                    // Порядковый номер плеча
                    $numWay = 0;

                    // Плечо
                    foreach ($AirlineOffer->PricedOffer->OfferPrice as $OfferPrice) {

                        // Сегменты плеча для типа пассажира
                        foreach ($OfferPrice->RequestedDate->Associations as $Associations) {

                            // Парсинг багажа
                            if (isset($Associations->ApplicableFlight, $Associations->ApplicableFlight->FlightSegmentReference)) {
                                // Ассоциация с пассажиром
                                $TravelerReferences = (string) $Associations->AssociatedTraveler->TravelerReferences;

                                /* @var $AnonymousTraveler Passenger */
                                $AnonymousTraveler = $AnonymousTravelerList[$TravelerReferences];

                                $typePassenger = str_replace('CNN', 'CHD', $AnonymousTraveler->getType());

                                // Добавление подкласса в сегменты предложения
                                foreach ($Associations->ApplicableFlight->FlightSegmentReference as $FlightSegmentReference) {

                                    if (isset($FlightSegmentReference->BagDetailAssociation, $FlightSegmentReference->BagDetailAssociation->CheckedBagReferences)) {
                                        $baggageRef = (string) $FlightSegmentReference->BagDetailAssociation->CheckedBagReferences;
                                        if (isset($baggageAllowanceList[$baggageRef]) && $baggageAllowanceList[$baggageRef] instanceof BaggageAllowance) {
                                            // Доступность багажа по сегментам у текущего плеча
                                            $baggageAssoc[$typePassenger][] = $baggageAllowanceList[$baggageRef];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Список сегментов
                    $segments = array();

                    // Плечо
                    foreach ($AirlineOffer->PricedOffer->OfferPrice as $OfferPrice) {

                        // Сегменты плеча для типа пассажира
                        foreach ($OfferPrice->RequestedDate->Associations as $Associations) {

                            // Добавление сегментов в бронь, ЕСЛИ НЕ установлены
                            if (isset($Associations->ApplicableFlight, $Associations->ApplicableFlight->FlightSegmentReference) && empty($booking->getSegmentsByNumWay($numWay))) {

                                // Добавление подкласса в сегменты предложения
                                foreach ($Associations->ApplicableFlight->FlightSegmentReference as $FlightSegmentReference) {

                                    if (isset($FlightSegmentReference['ref'])) {
                                        // Если сегмент не добавлен в список сегментов бронирования, то добавляем его
                                        if (empty($segments[(string) $FlightSegmentReference['ref']])) {
                                            $segments[(string) $FlightSegmentReference['ref']] = clone $FlightSegmentList[(string) $FlightSegmentReference['ref']];

                                            // Номер плеча
                                            $segments[(string) $FlightSegmentReference['ref']]->setWayNumber($numWay);
                                        }

                                        /* @var $segment S7AgentSegment */
                                        $segment = $segments[(string) $FlightSegmentReference['ref']];

                                        if (isset($FlightSegmentReference->ClassOfService)) {
                                            $segment->setSubClass((string) $FlightSegmentReference->ClassOfService->Code);
                                            $segment->setFareCode((string) $FlightSegmentReference->ClassOfService->MarketingName);
                                            $segment->addAllowedSeat((string) $FlightSegmentReference->ClassOfService->Code, (string) $FlightSegmentReference->ClassOfService->Code['SeatsLeft']);
                                        }

                                        if (isset($FlightSegmentReference->Cabin, $FlightSegmentReference->Cabin->CabinDesignator)) {
                                            // FIXME замена базового класса должна производиться после работы модуля
                                            $segment->setBaseClass(str_replace('B', 'C', (string) $FlightSegmentReference->Cabin->CabinDesignator));
                                        }
                                    }
                                }
                            }
                        }

                        // Прайсы плеча для типа пассажира
                        if (isset($OfferPrice->FareDetail, $OfferPrice->FareDetail->FareComponent)) {
                            foreach ($OfferPrice->FareDetail->FareComponent as $FareComponent) {
                                $BaseAmount = $FareComponent->PriceBreakdown->Price->BaseAmount;
                                $TotalTaxes = $FareComponent->PriceBreakdown->Price->Taxes->Total;

                                $BasePrice = new \RK_Core_Money((string) $BaseAmount, (string) $BaseAmount['Code']);
                                $TotalTaxesPrice = new \RK_Core_Money((string) $TotalTaxes, (string) $TotalTaxes['Code']);

                                // Соответствие тарифа типу пассажира
                                /* @var $AnonymousTraveler Passenger */
                                $AnonymousTraveler = $AnonymousTravelerList[(string) $FareComponent['refs']];

                                $typePassenger = str_replace('CNN', 'CHD', $AnonymousTraveler->getType());

                                try {
                                    $farePrice = $booking->getPriceByTypePassenger($typePassenger, false);

                                    if ($farePrice && $farePrice->getType() === $typePassenger) {
                                        $farePrice->setBaseFare( $farePrice->getBaseFare()->add($BasePrice) );
                                        $farePrice->setTotalFare( $farePrice->getTotalFare()->add($BasePrice)->add($TotalTaxesPrice) );  // Прибавление к базовому тарифу суммы такс, затем умножение на количество поссажиров данного (ADT, CHD или INF) типа

                                        if ($tax = $farePrice->getTaxByCode('total')) {
                                            // Сброс и переустановка такс
                                            $farePrice->setTaxes(array());
                                            $farePrice->addTax('total', $tax->getAmount()->add($TotalTaxesPrice));
                                        }

                                    } else {
                                        $farePrice = new S7AgentPrice();

                                        // Тип пассажира
                                        $farePrice->setType($typePassenger);

                                        // Багаж
                                        $farePrice->setBaggageAllowance($baggageAssoc[$typePassenger]);

                                        // Количество пассажиров данного типа
                                        $farePrice->setQuantity($AnonymousTraveler->getCount());

                                        $farePrice->setBaseFare($BasePrice);

                                        /*if ($EquivPrice) {
                                            $farePrice->setEquivFare($EquivPrice);
                                        }*/

                                        $farePrice->setTotalFare($BasePrice->add($TotalTaxesPrice));

                                        // FIXME в поиске S7 нет детализации такс
                                        $farePrice->addTax('total', $TotalTaxesPrice);

                                        // Добавление прайсов
                                        $booking->addPrice($farePrice->getType(), $farePrice);
                                    }

                                } catch (PassengerPriceNotSetException $e) {

                                } catch (\Exception $e) {
                                    // Различия в валюте при операции сложения
                                }
                            }
                        }

                        $numWay++;

                        /*
                        // Стоимость
                        //$TotalPrice = $AirlineOffer->TotalPrice->DetailCurrencyPrice->Total;
                        $FarePrice  = $OfferPrice->FareDetail->FareComponent->PriceBreakdown->Price;

                        // Таксы
                        foreach ($FarePrice->Taxes->Breakdown->Tax as $Tax) {
                            $farePrice->addTax((string) $Tax->TaxCode, new \RK_Core_Money((string) $Tax->Amount, (string) $Tax->Amount['Code']));
                        }

                        $BasePrice  = new \RK_Core_Money((string) $FarePrice->BaseAmount, (string) $FarePrice->BaseAmount['Code']);
                        $EquivPrice = null; // TODO найти и проверить формат данных у тарифов в валюте
                        $TotalPrice = $BasePrice->add($farePrice->getTaxesSum())->mult($farePrice->getQuantity());  // Прибавление к базовому тарифу суммы такс, затем умножение на количество поссажиров данного (ADT, CHD или INF) типа

                        $farePrice->setTotalFare($TotalPrice);
                        $farePrice->setBaseFare($BasePrice);
                        if ($EquivPrice) {
                            $farePrice->setEquivFare($EquivPrice);
                        }

                        //$refundable = (string) $AirPricingInfo['Refundable'];
                        //$farePrice->setRefundable(($refundable === 'true') ? true : false);

                        // Расчетная строка
                        $farePrice->setFareCalc((string) $AirlineOffer->PricedOffer->OfferPrice->FareDetail->Remarks->Remark);
                        */

                        // Добавление прайсов
                        //$booking->addPrice($farePrice->getType(), $farePrice);
                    }

                    // Установка сегментов
                    $booking->setSegments(array_values($segments));

                    // Валидирующая компания
                    $booking->setValidatingCompany((string) $AirlineOffer->OfferID['Owner']);

                    // Таймлимит
                    //$booking->setTimelimit(new \RK_Core_Date((string) $AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_SERVICES));

                    $results[] = $booking;
                }
            }
        }

        $this->setResult($results);

        return $results;
    }
}