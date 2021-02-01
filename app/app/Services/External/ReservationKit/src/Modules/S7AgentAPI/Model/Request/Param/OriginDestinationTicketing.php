<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

class OriginDestinationTicketing extends XmlElement
{
    /**
     * @param Segment[] $segments
     */
    public function __construct(array $segments)
    {
        // Распределение сегментов по плечам
        $waysList = array();
        foreach ($segments as $segment) {
            if (empty($waysList[$segment->getWayNumber()])) {
                $waysList[$segment->getWayNumber()] = array();
            }

            $waysList[(int) $segment->getWayNumber()][] = $segment;
        }

        $OriginDestinationList = array();
        $absNumSegment = 1;
        foreach ($waysList as $wayNum => $waySegments) {
            $flightReferences = [];
            foreach ($waySegments as $numSegment => $segment) {
                if ($numSegment === 0) {
                    $departureCode = $segment->getDepartureCode();
                }

                if ($numSegment === (count($waySegments) - 1)) {
                    $arrivalCode = $segment->getArrivalCode();
                }

                $flightReferences[] = 'SEG' . $absNumSegment;

                $absNumSegment++;
            }

            $OriginDestinationList[] = new XmlElement('OriginDestination', array('OriginDestinationKey' => 'OD' . ($wayNum + 1)), array(
                new XmlElement('DepartureCode', array(), $departureCode, 'ns1'),
                new XmlElement('ArrivalCode', array(), $arrivalCode, 'ns1'),
                new XmlElement('FlightReferences', array(), implode(' ', $flightReferences), 'ns1'),
            ), 'ns1');
        }

        parent::__construct(null, array(), $OriginDestinationList, 'ns1');
    }
}