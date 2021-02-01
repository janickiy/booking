<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;

class AirSegment extends XmlElement
{
    /**
     * @param Segment|GalileoSegment $segment
     */
    public function __construct(Segment $segment)
    {
        $attributesAirSegment = array(
            'Key'            => $segment->getKey(),
            'Group'          => $segment->getWayNumber(),
            'Carrier'        => $segment->getMarketingCarrierCode(),
            'FlightNumber'   => $segment->getFlightNumber(),
            'ProviderCode'   => '1G',
            'Origin'         => $segment->getDepartureCode(),
            'Destination'    => $segment->getArrivalCode(),
            'DepartureTime'  => $segment->getDepartureDate()->formatTo(\RK_Core_Date::DATE_FORMAT_ISO_8601),    // 2015-09-24T06:30:00.000+10:00
            'ArrivalTime'    => $segment->getArrivalDate()->formatTo(\RK_Core_Date::DATE_FORMAT_ISO_8601),      // 2015-09-24T08:05:00.000+10:00,
            //'FlightTime'     => $segment->getFlightTime(),
            //'TravelTime'     => $segment->getTravelTime(),
            //'Distance'       => $segment->getFlightDistance(),
            //'ClassOfService' => $segment->getSubClass(), // 'Q'
            //'ChangeOfPlane'  => $segment->getChangeOfPlane(),
            //'OptionalServicesIndicator' => $segment->getOptionalServicesIndicator(),
            //'AvailabilityDisplayType'   => $segment->getAvailabilityDisplayType(),

            //'AvailabilitySource' => 'S',
            //'Equipment' => '332',
            //'LinkAvailability' => 'true',
            //'ParticipantLevel' => 'Secure Sell',
            //'PolledAvailabilityOption' => 'Polled avail used',
        );

        if ($segment->getSubClass()) {
            $attributesAirSegment['ClassOfService'] = $segment->getSubClass();
        }

        $contentAirSegment = array();

        if ($segment instanceof GalileoSegment) {
            $codeshareInfoList = $segment->getCodeshareInfo();
            foreach ($codeshareInfoList as $keyOperatingCarrier => $codeshareInfo) {
                $contentAirSegment[] = new XmlElement('CodeshareInfo', array('OperatingCarrier' => $keyOperatingCarrier), $codeshareInfo, 'air');
            }

            if ($segment->isNeedConnectionToNextSegment()) {
                $contentAirSegment[] = new XmlElement('Connection', array(), null, 'air');
            }
        }

        $AirSegment = new XmlElement('AirSegment', $attributesAirSegment, $contentAirSegment, 'air');

        parent::__construct(null, array(), $AirSegment, 'air');
    }
}