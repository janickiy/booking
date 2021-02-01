<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class FareRulesRQ extends Request
{
    /**
     * @param \RK_Avia_Entity_Search_Request
     */
    public function __construct(\RK_Avia_Entity_Search_Request $booking, $price)
    {
        $this->addParam('Document',
            new XmlElement('Document', array(), null, 'ns1', true)
        );

        $this->addParam('Party',
            new XmlElement('Party', array(),
                new XmlElement('Sender', array(),
                    new AgentUserSender(false, false, '0.52')
                , 'ns1')
            , 'ns1')
        );

        $segments = $booking->getSegments();

        $flightNumber = null;
        if (count($segments) > 1) {
            $flightNumber = new XmlElement('Flight', array(), $booking->getSegment(0)->getFlightNumber(), 'ns1');
        }

        $this->addParam('Query',
            new XmlElement('Query', array(), array(
                new XmlElement('Departure', array('ObjectKey' => 'FN178'), array(
                    new XmlElement('AirportCode', array(), $booking->getSegment(0)->getDepartureCode(), 'ns1'),
                    new XmlElement('Date', array(), $booking->getSegment(0)->getDepartureDate()->formatTo('Y-m-d')->getValue(), 'ns1'),
                ), 'ns1'),

                new XmlElement('Arrival', array(), array(
                    new XmlElement('AirportCode', array(), $booking->getSegment(0)->getArrivalCode(), 'ns1')
                ), 'ns1'),

                new XmlElement('FareBasisCode', array(), array(
                    new XmlElement('Code', array(), $booking->getSegment(0)->getFareCode(), 'ns1')
                ), 'ns1'),

                new XmlElement('AirlineID', array(), 'S7', 'ns1'),

                new XmlElement('FareCode', array(), array(
                    new XmlElement('Code', array(), $booking->getSegment(0)->getSubClass(), 'ns1')
                ), 'ns1'),

            ), 'ns1')
        );

        $this->addParam('Metadata',
            new XmlElement('Metadata', array(),
                new XmlElement('Other', array(),
                    new XmlElement('OtherMetadata', array(),
                        new XmlElement('RuleMetadatas', array(),
                            new XmlElement('RuleMetadata', array('MetadataKey' => 'FIH1'), array(
                                new XmlElement('RuleID', array(), 'flightInfoHash', 'ns1'),
                                new XmlElement('Remarks', array(),
                                    new XmlElement('Remark', array(), $price->getFareCalc(), 'ns1')
                                , 'ns1'),
                            ), 'ns1')
                        , 'ns1')
                    , 'ns1')
                , 'ns1')
            , 'ns1')
        );

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'xmlns:ns1' => 'http://www.iata.org/IATA/EDIST',
            'Version'   => '1.0'
        );
    }

    public function getWSDLServiceName()
    {
        return 'FareRulesRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'findRules';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}