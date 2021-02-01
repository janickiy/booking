<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;

use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking;
use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price as GalileoPrice;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Avia\Model\Entity\FareInfo;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Requisites;

class LowFareSearchRsp extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->LowFareSearchRsp)) {
            $body = $this->getResponse()->Body->LowFareSearchRsp;
        } else {
            throw new GalileoException('Bad LowFareSearch response content');
        }

        // Разбор параметров
        $results = array();

        // Брендирование
        if (isset($body->BrandList)) {
            $BrandList = array();

            foreach ($body->BrandList->Brand as $Brand) {
                $BrandFares = new Brand();
                $BrandFares->setBrandID((string) $Brand['BrandID']);
                $BrandFares->setName((string) $Brand['Name']);
                $BrandFares->setCarrier((string) $Brand['Carrier']);
                //$BrandFares->setBrandedDetailsAvailable((string) $Brand['BrandedDetailsAvailable']);

                // Список заголовков
                foreach ($Brand->Title as $title) {
                    $BrandFares->addTitle((string) $title['Type'], (string) $title);
                }
                // Список текстов
                foreach ($Brand->Text as $text) {
                    $BrandFares->addText((string) $text['Type'], (string) $text);
                }

                $BrandList[(string) $Brand['BrandID']] = $BrandFares;
            }
        }

        // Информация о тарифе
        $FareInfoList = array();
        foreach ($body->FareInfoList->FareInfo as $AirFareInfo) {
            $FareInfo = new GalileoFareInfo();

            // Код тарифа
            $FareInfo->setFareCode((string) $AirFareInfo['FareBasis']);

            // Багаж (по количеству)
            if (isset($AirFareInfo->BaggageAllowance->NumberOfPieces)) {
                $NumberOfPieces = (string) $AirFareInfo->BaggageAllowance->NumberOfPieces;
                $FareInfo->setBaggageAllowance($NumberOfPieces . 'PC');
            }

            // Багаж (по весу)
            if (isset($AirFareInfo->BaggageAllowance->MaxWeight,
                      $AirFareInfo->BaggageAllowance->MaxWeight['Value'],
                      $AirFareInfo->BaggageAllowance->MaxWeight['Unit'])) {
                $Value = (string) $AirFareInfo->BaggageAllowance->MaxWeight['Value'];
                $Unit  = (string) $AirFareInfo->BaggageAllowance->MaxWeight['Unit'];

                $FareInfo->setBaggageAllowance($Value . str_replace(array('Kilograms'), array('K'), $Unit));
            }

            // Тур-код
            if (isset($AirFareInfo['TourCode'])) {
                $FareInfo->setTourCode((string) $AirFareInfo['TourCode']);
            }

            // Ключи для правил
            $FareInfo->setRuleKey((string) $AirFareInfo->FareRuleKey);

            // Указатель скидки
            $FareInfo->setFareTicketDesignator((string) $AirFareInfo->FareTicketDesignator['Value']);

            // Информация о бренде
            if (isset($AirFareInfo->Brand, $AirFareInfo->Brand['BrandID'], $BrandList)) {
                $BrandID = (string) $AirFareInfo->Brand['BrandID'];
                $FareInfo->setBrand($BrandList[$BrandID]);
            }

            $FareInfoList[(string) $AirFareInfo['Key']] = $FareInfo;
        }

        // Сегменты
        $i = 0;
        $AirSegmentList = array();
        foreach ($body->AirSegmentList->AirSegment as $AirSegment) {
            $segment = new GalileoSegment();

            $segment->setKey((string) $AirSegment['Key']);

            $segment->setWayNumber((string) $AirSegment['Group']);

            // Отправление
            $segment->setDepartureCode((string) $AirSegment['Origin']);
            $segment->setDepartureDate(new \RK_Core_Date((string) $AirSegment['DepartureTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601));
            $segment->setDepartureTerminal((string) $body->FlightDetailsList->FlightDetails[$i]['OriginTerminal']);

            // Прибытие
            $segment->setArrivalCode((string) $AirSegment['Destination']);
            $segment->setArrivalDate(new \RK_Core_Date((string) $AirSegment['ArrivalTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601));
            $segment->setArrivalTerminal((string) $body->FlightDetailsList->FlightDetails[$i]['DestinationTerminal']);

            // Оперирующая компания
            $operatingCarrier = isset($AirSegment->CodeshareInfo) ? (string) $AirSegment->CodeshareInfo['OperatingCarrier'] : (string) $AirSegment['Carrier'];
            $segment->setOperationCarrierCode($operatingCarrier);

            // Маркетинговая компания
            $segment->setMarketingCarrierCode((string) $AirSegment['Carrier']);

            // Номер рейса
            $segment->setFlightNumber((string) $AirSegment['FlightNumber']);

            // Время перелета
            $segment->setFlightTime((string) $AirSegment['FlightTime']);

            // Дальность перелета
            $segment->setFlightDistance((string) $AirSegment['Distance']);

            // Номер борта
            $segment->setAircraftCode((string) $AirSegment['Equipment']);

            //$segment->setOptionalServicesIndicator((string) $AirSegment['OptionalServicesIndicator']);
            //$segment->setChangeOfPlane((string) $AirSegment['ChangeOfPlane']);
            //$segment->setTravelTime((string) $body->FlightDetailsList->FlightDetails[$i]['TravelTime']);

            $AirSegmentList[(string) $AirSegment['Key']] = $segment;

            // Счетчик сегментов
            $i++;
        }

        // Прайсы
        $AirPricingSolutionList = array();
        foreach ($body->AirPricingSolution as $AirPricingSolution) {
            $booking = new Booking();

            // Прайсы для типов пассажиров
            foreach ($AirPricingSolution->AirPricingInfo as $AirPricingInfo) {
                $farePrice = new GalileoPrice();

                // Тип пассажира
                $farePrice->setType(str_replace('CNN', 'CHD', (string) $AirPricingInfo->PassengerType['Code']));

                // Количество пассажиров данного типа
                $farePrice->setQuantity(count($AirPricingInfo->PassengerType));

                // Стоимость
                $TotalPrice = (string) $AirPricingInfo['TotalPrice'];
                $BasePrice  = (string) $AirPricingInfo['BasePrice'];
                $EquivPrice = isset($AirPricingInfo['EquivalentBasePrice']) ? (string) $AirPricingInfo['EquivalentBasePrice'] : null;

                $farePrice->setTotalFare(MoneyHelper::parseMoneyString($TotalPrice));
                $farePrice->setBaseFare(MoneyHelper::parseMoneyString($BasePrice));
                if ($EquivPrice) {
                    $farePrice->setEquivFare(MoneyHelper::parseMoneyString($EquivPrice));
                }

                // Таксы
                if (isset($AirPricingInfo->TaxInfo)) {
                    foreach ($AirPricingInfo->TaxInfo as $TaxInfo) {
                        $farePrice->addTax((string) $TaxInfo['Category'], MoneyHelper::parseMoneyString((string) $TaxInfo['Amount']));
                    }
                }

                $refundable = (string) $AirPricingInfo['Refundable'];
                $farePrice->setRefundable(($refundable === 'true') ? true : false);

                // Расчетная строка
                $farePrice->setFareCalc((string) $AirPricingInfo->FareCalc);

                // Добавление прайсов
                $booking->addPrice($farePrice->getType(), $farePrice);

                // Валидирующая компания
                $booking->setValidatingCompany((string) $AirPricingInfo['PlatingCarrier']);

                // Таймлимит
                $booking->setTimelimit(new \RK_Core_Date((string) $AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_SERVICES));
            }

            $connections = array();
            if (isset($AirPricingSolution->Connection)) {
                foreach ($AirPricingSolution->Connection as $Connection) {
                    $connections[(string) $Connection['SegmentIndex']] = (string) $Connection['SegmentIndex'];
                }
            }
            
            // Количество мест в сегменте (берется только для первого пассажира)
            foreach ($AirPricingSolution->AirPricingInfo[0]->BookingInfo as $BookingInfo) {
                $SegmentRef = (string) $BookingInfo['SegmentRef'];
                $FareInfoRef = (string) $BookingInfo['FareInfoRef'];

                /* @var Segment $segment */
                $segment = $AirSegmentList[$SegmentRef];
                $segment->setKey($SegmentRef);
                $segment->setFareInfoRef($FareInfoRef);

                $segment->addAllowedSeat((string) $BookingInfo['BookingCode'], (string) $BookingInfo['BookingCount']);

                /* @var GalileoFareInfo $fareInfo */
                $fareInfo = $FareInfoList[$FareInfoRef];
                $segment->setBaggage($fareInfo->getBaggageAllowance());

                // Код тарифа
                $segment->setFareCode($fareInfo->getFareCode());    //$segment->setBaseFare($fareInfo->getFareCode());

                // Тип класса сегмента
                $segment->setTypeClass((string) $BookingInfo['CabinClass']);
                // Базовый класс сегмента
                $segment->setBaseClass(Request::getBaseClassByType($segment->getTypeClass()));
                // Подкласс сегмента
                $segment->setSubClass((string) $BookingInfo['BookingCode']);
            }

            // Добавление сегментов
            $numJourney = 0;
            $numSegment = 0;
            foreach ($AirPricingSolution->Journey as $Journey) {
                foreach ($Journey->AirSegmentRef as $AirSegmentRef) {
                    $SegmentRef = (string) $AirSegmentRef['Key'];

                    /* @var Segment $segment */
                    $segment = $AirSegmentList[$SegmentRef];
                    $segment->setWayNumber($numJourney);

                    $booking->addSegment(clone $segment);

                    // Если есть соединение для сегмента, то устанавливаем его
                    if (isset($connections[$numSegment])) {
                        $booking->getSegment($numSegment)->setNeedConnectionToNextSegment(true);
                    }

                    // Расчет времени пересадки между сегментами
                    if ($booking->getSegment($numSegment + 1) &&
                        $booking->getSegment($numSegment + 1)->getWayNumber() === $booking->getSegment($numSegment)->getWayNumber()) {

                        // Время пересадки
                        $currentSegment = $booking->getSegment($numSegment);
                        $nextSegment = $booking->getSegment($numSegment + 1);

                        $arrivalDateTime = $currentSegment->getArrivalDate()->getDateTime();
                        $departureDateTime = $nextSegment->getDepartureDate()->getDateTime();

                        $transferDiff = $departureDateTime->diff($arrivalDateTime);

                        $booking->getSegment($numSegment)->setTransferTime($transferDiff);
                    }

                    $numSegment++;
                }

                $numJourney++;
            }

            // Добавление реквизитов
            $booking->setRequisiteRules(Requisites::getInstance()->getRules());

            $results[] = $booking;
        }

        $this->setResult($results);

        return $results;
    }

}