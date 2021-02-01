<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;

use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Galileo\Model\Entity\BookingCodeInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price as GalileoPrice;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Avia\Model\Entity\FareInfo;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response as GalileoResponse;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Requisites;

class AvailabilitySearchRsp extends GalileoResponse
{
    private $_nextResultReference;

    public function __construct($response)
    {
        $this->setResponse($response);
    }

    /**
     * @return string
     */
    public function getNextResultReference()
    {
        return $this->_nextResultReference;
    }

    /**
     * @param string $nextResultReference
     */
    public function setNextResultReference($nextResultReference)
    {
        $this->_nextResultReference = $nextResultReference;
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->AvailabilitySearchRsp)) {
            $body = $this->getResponse()->Body->AvailabilitySearchRsp;
        } else {
            throw new GalileoException('Bad AvailabilitySearchRsp response content');
        }

        // Разбор параметров
        $results = array();

        // Ссылка для получения следующих результатов
        if (isset($body->NextResultReference)) {
            $this->setNextResultReference((string) $body->NextResultReference);
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

            // Доступные подклассы
            $bookingCodeInfoList = array();
            foreach ($AirSegment->AirAvailInfo->BookingCodeInfo as $XmlBookingCodeInfo) {
                $bookingCodeInfo = new BookingCodeInfo();
                $bookingCodeInfo->setCabinClass((string) $XmlBookingCodeInfo['CabinClass']);
                $bookingCodeInfo->setBookingCounts((string) $XmlBookingCodeInfo['BookingCounts']);

                $bookingCodeInfoList[] = $bookingCodeInfo;
            }

            $segment->setAirAvailInfo($bookingCodeInfoList);

            $AirSegmentList[(string) $AirSegment['Key']] = $segment;

            // Счетчик сегментов
            $i++;
        }

        // Объединение сегментов в маршруты
        $numAirItinerarySolution = 0;
        $airItinerarySolutionList = array();
        foreach ($body->AirItinerarySolution as $AirItinerarySolution) {
            // Список индексов сегментов, которые необходимо соединить со следующим сегментом с помощью элемента
            $connectionsList = array();
            if (isset($AirItinerarySolution->Connection)) {
                foreach ($AirItinerarySolution->Connection as $Connection) {
                    $connectionsList[(string) $Connection['SegmentIndex']] = (string) $Connection['SegmentIndex'];
                }
            }

            $numAirSegmentRef = 0;
            $isConnectSegment = false;
            foreach ($AirItinerarySolution->AirSegmentRef as $AirSegmentRef) {
                // Поиск соответствующего сегмента
                if (isset($AirSegmentList[(string) $AirSegmentRef['Key']])) {
                    /** @var GalileoSegment $segment */
                    $segment = $AirSegmentList[(string) $AirSegmentRef['Key']];
                } else {
                    continue;
                }

                if ($isConnectSegment) {
                    $numLastVariant = count($airItinerarySolutionList[$segment->getWayNumber()]) - 1;
                    $airItinerarySolutionList[$segment->getWayNumber()][$numLastVariant][] = $segment;

                    $isConnectSegment = false;
                } else {
                    $airItinerarySolutionList[$segment->getWayNumber()][] = array($segment);
                }

                // Если есть соединение для сегмента, то следующий сегмент надо присоединить к текущему
                if (isset($connectionsList[$numAirSegmentRef])) {
                    $segment->setNeedConnectionToNextSegment(true);
                    $isConnectSegment = true;
                }

                $numAirSegmentRef++;
            }

            $numAirItinerarySolution++;
        }

        $searchPCC = Requisites::getInstance()->getRules()->getSearchPCC();

        $this->setResult(array($searchPCC => $airItinerarySolutionList));

        return $this->getResult();
    }

    /**
     * @param string $key
     * @param string $wayNumber
     * @param GalileoSegment[] $airSegmentList
     * @return null|GalileoSegment
     */
    private function findSegmentBy($key, $wayNumber, $airSegmentList)
    {
        foreach ($airSegmentList as $segment) {
            if ($segment->getKey() === $key && $segment->getWayNumber() === $wayNumber) {
                return $segment;
            }
        }

        return null;
    }
}