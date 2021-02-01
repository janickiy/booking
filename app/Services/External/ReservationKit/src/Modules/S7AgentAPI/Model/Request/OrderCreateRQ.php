<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class OrderCreateRQ extends Request
{
    /**
     * @param \RK_Avia_Entity_Booking $booking
     */
    public function __construct(\RK_Avia_Entity_Booking $booking)
    {
        $this->addParam('Document',
            new XmlElement('Document', array(), null
                //new XmlElement('Name', array(), '1.0'),
            , 'ns1', true)
        );

        $this->addParam('Party',
            new XmlElement('Party', array(),
                new XmlElement('Sender', array(),
                    new AgentUserSender(true, false, '0.52')
                , 'ns1')
            , 'ns1')
        );

        // 3-х стронний договор
        $InstructionsList = null;
        if ($triPartyAgreement = $booking->getTriPartyAgreementByCarrierCode('S7')) {
            $InstructionsList =
                new XmlElement('InstructionsList', array(),
                    new XmlElement('Instruction', array('ListKey' => 'CC'),
                        new XmlElement('SpecialBookingInstruction', array(), array(
                            new XmlElement('Code', array(), $triPartyAgreement->getTourCode(), 'ns1'),
                            new XmlElement('Definition', array(), $triPartyAgreement->getAccountCode(), 'ns1')
                        ), 'ns1')
                    , 'ns1')
                , 'ns1');
        }

        $DataLists = null;
        if (isset($InstructionsList)) {
            $DataLists = new XmlElement('DataLists', array(), array(
                $InstructionsList,
            ), 'ns1');
        }

        $this->addParam('Query',
            new XmlElement('Query', array(), array(
                new XmlElement('Passengers', array(),
                    HelperRequest::getListRequestParam('PassengerForBooking', $booking->getPassengers())
                , 'ns1'),

                new XmlElement('OrderItems', array(), array(
                    new XmlElement('ShoppingResponse', array(), array(
                        new XmlElement('Owner', array(), 'S7', 'ns1'),
                        new XmlElement('ResponseID', array(), 'UNKNOWN', 'ns1'),
                        new XmlElement('Offers', array(),
                            new XmlElement('Offer', array(), array(
                                new XmlElement('OfferID', array('Owner' => 'S7'), 'UNKNOWN', 'ns1'),
                                new XmlElement('OfferItems', array(),
                                    new XmlElement('OfferItem', array(), array(
                                        new XmlElement('OfferItemID', array('Owner' => 'S7'), 'UNKNOWN', 'ns1'),
                                        new XmlElement('Passengers', array(),
                                            HelperRequest::buildRequestParam('PassengerReference', $booking->getPassengers())
                                        , 'ns1'),
                                        new XmlElement('ApplicableFlight', array(),
                                            HelperRequest::buildRequestParam('FlightReferences', $booking->getSegments())
                                        , 'ns1')
                                    ), 'ns1')
                                , 'ns1')
                            ), 'ns1')
                        , 'ns1'),
                    ), 'ns1'),

                    new XmlElement('OfferItem', array(), array(
                        new XmlElement('OfferItemID', array('Owner' => 'S7'), 'UNKNOWN', 'ns1'),
                        new XmlElement('OfferItemType', array(),
                            HelperRequest::buildRequestParam('DetailedFlightItem', $booking->getSegments())
                        , 'ns1')
                    ), 'ns1'),
                ), 'ns1'),

                $DataLists

            ), 'ns1')
        );

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'xmlns:ns1' => 'http://www.iata.org/IATA/EDIST',
            'Version'   => ''
        );
    }

    public function getWSDLServiceName()
    {
        return 'OrderCreateRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'book';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}