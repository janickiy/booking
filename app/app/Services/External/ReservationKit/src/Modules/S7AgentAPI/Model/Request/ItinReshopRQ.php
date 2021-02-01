<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Booking as S7AgentBooking;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class ItinReshopRQ extends Request
{
    /**
     * TODO переименовать $searchRequest, т.к. для Reprice with PNR передается объект Booking
     *
     * @param \RK_Avia_Entity_Search_Request|S7AgentBooking $searchRequest
     */
    public function __construct($searchRequest)
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

        // 3D договор
        $Qualifiers = null;
        if ($triPartyAgreement = $searchRequest->getTriPartyAgreementByCarrierCode('S7')) {
            $Qualifiers =
                new XmlElement('Qualifiers', array(),
                    new XmlElement('Qualifier', array(),
                        new XmlElement('SpecialFareQualifiers', array(), array(
                            new XmlElement('AirlineID', array(), $triPartyAgreement->getCarrier(), 'ns1'),
                            new XmlElement('CompanyIndex', array(), $triPartyAgreement->getTourCode(), 'ns1'),
                            new XmlElement('Account', array(), $triPartyAgreement->getAccountCode(), 'ns1')
                        ), 'ns1')
                    , 'ns1')
                , 'ns1');
        }

        // Reprice with PNR
        $BookingReferences = null;
        if ($searchRequest instanceof S7AgentBooking) {
            $BookingReferences =
                new XmlElement('BookingReferences', array(), array(
                    new XmlElement('BookingReference', array(), array(
                        new XmlElement('ID', array(), $searchRequest->getLocator(), 'ns1'),
                        new XmlElement('AirlineID', array(), 'S7', 'ns1'),
                    ), 'ns1'),
                ), 'ns1');
        }

        $this->addParam('Query',
            new XmlElement('Query', array(),
                new XmlElement('Reshop', array(),
                    new XmlElement('Actions', array(),
                        array(
                            new XmlElement('ActionType', array(), null, 'ns1', true),

                            $BookingReferences,

                            new XmlElement('OrderItems', array(),
                                new XmlElement('OrderItem', array(),
                                    array(
                                        new XmlElement('FlightItem', array(), array(
                                            HelperRequest::buildRequestParam('OriginDestinations', $searchRequest->getSegments()),

                                            new XmlElement('FareDetail', array(),
                                                HelperRequest::getListRequestParam('FareComponent', $searchRequest->getSegments())
                                            , 'ns1')
                                        ), 'ns1'),

                                        // TODO если после окончания разработки не будет других блоков Associations_* , то переименовать Associations_Passengers
                                        HelperRequest::buildRequestParam('Associations_Passengers', $searchRequest->getPassengers())
                                    )
                                , 'ns1')
                            , 'ns1'),

                            new XmlElement('Passengers', array(),
                                HelperRequest::getListRequestParam('Passenger', $searchRequest->getPassengers())
                            , 'ns1'),

                            $Qualifiers
                        )
                    , 'ns1')
                , 'ns1')
            , 'ns1')
        );

        parent::__construct();
    }

    /**
     * @return array
     */
    public function getFunctionAttributes()
    {
        return array(
            'xmlns:ns1' => 'http://www.iata.org/IATA/EDIST',
            'Version'   => '1.0'
        );
    }

    public function getWSDLServiceName()
    {
        return 'ItinReshopRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'reprice';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}