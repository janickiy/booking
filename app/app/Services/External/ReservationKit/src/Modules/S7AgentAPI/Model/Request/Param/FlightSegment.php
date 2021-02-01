<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment;

class FlightSegment extends XmlElement
{
    /**
     * @param Segment $segment
     * @param null $segmentNum
     */
    public function __construct(Segment $segment, $segmentNum = null)
    {
        parent::__construct('FlightSegment', array('SegmentKey' => 'SEG' . ($segmentNum + 1)), array(
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

            new XmlElement('ClassOfService', array(), array(
                new XmlElement('Code', array(), $segment->getSubClass(), 'ns1'),
                new XmlElement('MarketingName', array(), $segment->getFareCode(), 'ns1')
            ), 'ns1')

        ), 'ns1');
    }
}