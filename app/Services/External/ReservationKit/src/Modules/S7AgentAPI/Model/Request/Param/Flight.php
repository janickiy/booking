<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment;

// TODO flight for price/reprice
class Flight extends XmlElement
{
    /**
     * @param Segment $segment
     * @param null $segmentNum
     */
    public function __construct(Segment $segment, $segmentNum = null)
    {
        parent::__construct('Flight', array(), array(
            new XmlElement('SegmentKey', array(), 'FL' . ($segment->getId() + 1), 'ns1'),
            new XmlElement('Departure', array(), array(
                new XmlElement('AirportCode', array(), $segment->getDepartureCode(), 'ns1'),
                new XmlElement('Date', array(), $segment->getDepartureDate()->formatTo('Y-m-d')->getValue(), 'ns1'),
                new XmlElement('Time', array(), $segment->getDepartureDate()->formatTo('H:i')->getValue(), 'ns1'),
            ), 'ns1'),

            new XmlElement('Arrival', array(), array(
                new XmlElement('AirportCode', array(), $segment->getArrivalCode(), 'ns1'),
                new XmlElement('Date', array(), $segment->getArrivalDate()->formatTo('Y-m-d')->getValue(), 'ns1'),
                new XmlElement('Time', array(), $segment->getArrivalDate()->formatTo('H:i')->getValue(), 'ns1'),
            ), 'ns1'),

            new XmlElement('MarketingCarrier', array(), array(
                new XmlElement('AirlineID', array(), $segment->getMarketingCarrierCode(), 'ns1'),
                new XmlElement('FlightNumber', array(), str_pad($segment->getFlightNumber(), 3, '0', STR_PAD_LEFT), 'ns1'),
            ), 'ns1'),

            new XmlElement('OperatingCarrier', array(), array(
                new XmlElement('AirlineID', array(), $segment->getOperationCarrierCode(), 'ns1'),
                new XmlElement('FlightNumber', array(), str_pad($segment->getFlightNumber(), 3, '0', STR_PAD_LEFT), 'ns1'),
            ), 'ns1')

        ), 'ns1');
    }
}