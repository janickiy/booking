<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class ServicePriceRQ extends Request
{
    /**
     * @param \RK_Avia_Entity_Search_Request $searchRequest
     */
    public function __construct(\RK_Avia_Entity_Search_Request $searchRequest)
    {
        $this->addParam('Document',
            new XmlElement('Document', array(), null, 'ns1', true)
        );

        $this->addParam('Party',
            new XmlElement('Party', array(),
                new XmlElement('Sender', array(),
                    new AgentUserSender()
                , 'ns1')
            , 'ns1')
        );

        $this->addParam('Parameters',
            new XmlElement('Parameters', array(),
                new XmlElement('ServiceFilters', array(), array(

                    new XmlElement('ServiceFilter', array(),
                        new XmlElement('GroupCode', array(), 'baggage', 'ns1')
                    , 'ns1'),

                    new XmlElement('ServiceFilter', array(),
                        new XmlElement('GroupCode', array(), 'SEATS', 'ns1')
                    , 'ns1'),

                    new XmlElement('ServiceFilter', array(),
                        new XmlElement('GroupCode', array(), 'mEaLs', 'ns1')
                    , 'ns1'),

                    new XmlElement('ServiceFilter', array(),
                        new XmlElement('GroupCode', array(), 'OtHeR', 'ns1')
                    , 'ns1'),

                ), 'ns1')
            , 'ns1')
        );

        $this->addParam('Travelers',
            new XmlElement('Travelers', array(),
                HelperRequest::getListRequestParam('Traveler', $searchRequest->getPassengers())
            , 'ns1')
        );

        $this->addParam('Query',
            new XmlElement('Query', array(),
                HelperRequest::buildRequestParam('OriginDestinations', $searchRequest->getSegments())
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
        return 'ServicePriceRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'servicePrice';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}