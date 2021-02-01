<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Helper\SearchRequestOrBookingHelper;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class FlightPriceRQ extends Request
{
    /**
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     */
    public function __construct($searchRequestOrBooking)
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

        $this->addParam('Parameters',
            new XmlElement('Parameters', array(), array(
                new XmlElement('CurrCodes', array(),
                    // RUB, USD, EUR, CNY, KZT
                    new XmlElement('CurrCode', array(), 'RUB', 'ns1')
                , 'ns1'),

                new XmlElement('ServiceFilters', array(),
                    new XmlElement('ServiceFilter', array(), array(
                        new XmlElement('GroupCode', array(), 'FareRules', 'ns1'),
                        new XmlElement('SubGroupCode', array(), 'true', 'ns1'),
                    ), 'ns1')
                , 'ns1'),
            ), 'ns1')
        );

        $this->addParam('Travelers',
            new XmlElement('Travelers', array(),
                HelperRequest::getListRequestParam('Traveler', SearchRequestOrBookingHelper::getAnonymousPassengers($searchRequestOrBooking))
            , 'ns1')
        );

        $this->addParam('Query',
            new XmlElement('Query', array(),
                HelperRequest::buildRequestParam('OriginDestinations', $searchRequestOrBooking->getSegments())
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
        return 'FlightPriceRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'flightInfo';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}