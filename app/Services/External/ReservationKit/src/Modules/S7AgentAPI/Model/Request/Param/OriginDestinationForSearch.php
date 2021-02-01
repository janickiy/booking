<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;

class OriginDestinationForSearch extends XmlElement
{
    /**
     * @param Segment $segment
     */
    public function __construct(Segment $segment)
    {
        $departureDate = null;
        if ($segment->getDepartureDate()) {
            $departureDate = new XmlElement('Date', array(), $segment->getDepartureDate()->formatTo('Y-m-d')->getValue(), 'ns1');
        }

        $arrivalDate = null;
        if ($segment->getArrivalDate()) {
            $arrivalDate = new XmlElement('Date', array(), $segment->getArrivalDate()->formatTo('Y-m-d')->getValue(), 'ns1');
        }

        parent::__construct('OriginDestination', array(), array(
            new XmlElement('Departure', array(), array(
                new XmlElement('AirportCode', array(), $segment->getDepartureCode(), 'ns1'),
                $departureDate
            ), 'ns1'),

            new XmlElement('Arrival', array(), array(
                new XmlElement('AirportCode', array(), $segment->getArrivalCode(), 'ns1'),
                $arrivalDate
            ), 'ns1')
        ), 'ns1');
    }
}