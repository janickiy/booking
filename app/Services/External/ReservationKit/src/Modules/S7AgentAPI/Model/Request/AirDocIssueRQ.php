<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Booking as S7AgentBooking;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Passenger as S7AgentPassenger;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

use ReservationKit\src\Modules\Avia\Model\Exception\PassengerPriceNotSetException;

class AirDocIssueRQ extends Request
{
    /**
     * AirDocIssueRQ constructor.
     * @param S7AgentBooking $booking
     * @param $passenger
     * @throws PassengerPriceNotSetException
     * @throws \RK_Core_Exception
     */
    public function __construct(S7AgentBooking $booking/*, S7AgentPassenger $passenger*/)
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

        //
        //$price = $booking->getPriceByTypePassenger($passenger->getType());

        //
        /*
        $ticketDesignatorFC1 = null;
        if ($price->getType() === 'CHD' || $price->getType() === 'INF') {
            $typeAssoc = array(
                'CHD' => 'CH',
                'INF' => 'IN'
            );

            $ticketDesignatorOptions = array(
                'type' => $typeAssoc[$price->getType()],
                'values' => $price->getTicketDesignator()
            );

            $ticketDesignatorFC1 = $ticketDesignator = new XmlElement('TicketDesig', array('Application' => $ticketDesignatorOptions['type']), $ticketDesignatorOptions['values'][0], 'ns1');
        }
        */

        //
        $paymentRemark = '*A*TRIVAGO';

        // 3-х стронний договор
        $InstructionsList = null;
        if ($triPartyAgreement = $booking->getTriPartyAgreementByCarrierCode('S7')) {
            $InstructionsList =
                new XmlElement('InstructionsList', array(),
                    new XmlElement('Instruction', array('ListKey' => 'CC'),
                        new XmlElement('SpecialFareQualifiers', array(), array(
                            new XmlElement('Code', array(), $triPartyAgreement->getTourCode(), 'ns1'),
                            new XmlElement('Definition', array(), $triPartyAgreement->getAccountCode(), 'ns1')
                        ), 'ns1')
                    , 'ns1')
                , 'ns1');

            $paymentRemark = '*' . $triPartyAgreement->getTourCode();
        }

        $this->addParam('Query',
            new XmlElement('Query', array(), array(
                new XmlElement('TicketDocQuantity', array(), '1', 'ns1'),

                new XmlElement('TicketDocInfo', array(), array(
                    new XmlElement('TravelerInfo', array(), array(
                        new XmlElement('Surname', array(), 'ANY'/*$passenger->getLastname()*/, 'ns1'),
                        new XmlElement('Given', array(), 'ANY'/*$passenger->getFirstname()*/, 'ns1'),
                        //new XmlElement('PTC', array(), $passenger->getType(), 'ns1'),
                    ), 'ns1'),

                    new XmlElement('BookingReference', array(/*'ObjectKey' => 'RPH' . $passenger->getRPH()*/), array(
                        /*
                        new XmlElement('Type', array(),
                            new XmlElement('Code', array(), '14', 'ns1')
                        , 'ns1'),
                        */
                        new XmlElement('ID', array(), $booking->getLocator(), 'ns1'),
                        new XmlElement('AirlineID', array(), 'S7', 'ns1'),
                    ), 'ns1'),

                    new XmlElement('Payments', array(), array(
                        new XmlElement('Payment', array('ObjectKey' => 'ETK'), array(
                            new XmlElement('Type', array(),
                                new XmlElement('Code', array(), 'MS', 'ns1')
                            , 'ns1'),

                            //new XmlElement('Amount', array('Code' => $price->getTotalFare()->getCurrency()), $price->getTotalFare()->getAmount('VAL'), 'ns1'),

                            new XmlElement('Other', array(),
                                new XmlElement('Remarks', array(),
                                    new XmlElement('Remark', array(), $paymentRemark, 'ns1')
                                , 'ns1')
                            , 'ns1'),
                        ), 'ns1'),
                    ), 'ns1'),
                ), 'ns1'),

                /*
                new XmlElement('DataLists', array(), array(
                    new XmlElement('CheckedBagAllowanceList', array(),
                        HelperRequest::getListRequestParam('CheckedBagAllowance', $price->getBaggageAllowance())
                    , 'ns1'),

                    new XmlElement('FareList', array(),
                        new XmlElement('FareGroup', array('refs' => 'ETK', 'ListKey' => 'FG1'), array(
                            new XmlElement('Fare', array(), array(
                                new XmlElement('FareCode', array(),
                                    new XmlElement('Code', array(), 'ANY', 'ns1')
                                , 'ns1'),

                                new XmlElement('FareDetail', array(), array_merge(
                                    array(
                                    new XmlElement('FareComponent', array('refs' => 'SEG1', 'ObjectKey' => 'FC1'), array(
                                        new XmlElement('PriceBreakdown', array(),
                                            new XmlElement('Price', array(), array(
                                                new XmlElement('BaseAmount', array('Code' => $price->getBaseFare()->getCurrency()), $price->getBaseFare()->getAmount('VAL'), 'ns1'),

                                                new XmlElement('FareFiledIn', array(), array(
                                                    new XmlElement('BaseAmount', array('Code' => $price->getEquivFare()->getCurrency()), $price->getEquivFare()->getAmount('VAL'), 'ns1'),
                                                    new XmlElement('ExchangeRate', array(), $price->getExchangeRate(), 'ns1', false),
                                                ), 'ns1'),

                                                new XmlElement('Surcharges', array(),
                                                    new XmlElement('Surcharge', array(),
                                                        new XmlElement('Total', array('Code' => $price->getTotalSurcharge()->getCurrency()), $price->getTotalSurcharge()->getAmount('VAL'), 'ns1', false),
                                                    'ns1', false),
                                                'ns1', false),

                                                new XmlElement('Taxes', array(), array(
                                                    new XmlElement('Total', array('Code' => $price->getTaxesSum()->getCurrency()), $price->getTaxesSum()->getAmount('VAL'), 'ns1'),

                                                    new XmlElement('Breakdown', array(),
                                                        HelperRequest::getListRequestParam('TaxXmlElement', $price->getTaxes())
                                                    , 'ns1', false)
                                                ), 'ns1')
                                            ), 'ns1')
                                        , 'ns1'),

                                        new XmlElement('FareBasis', array(),
                                            new XmlElement('FareBasisCode', array(),
                                                new XmlElement('Code', array(), $booking->getFirstSegment()->getFareCode(), 'ns1')
                                            , 'ns1')
                                        , 'ns1'),

                                        $ticketDesignatorFC1,

                                        new XmlElement('FareRules', array(),
                                            new XmlElement('Ticketing', array(),
                                                new XmlElement('Endorsements', array(),
                                                    HelperRequest::getListRequestParam('Endorsement', $price->getEndorsments(true))
                                                , 'ns1')
                                            , 'ns1')
                                        , 'ns1')
                                    ), 'ns1'),
                                    ),

                                    HelperRequest::getListRequestParam('FareComponentForTicketing', $booking->getSegments(), $ticketDesignatorOptions),

                                    array(
                                    new XmlElement('Remarks', array(),
                                        new XmlElement('Remark', array(), $price->getFareCalc(), 'ns1')
                                    , 'ns1')
                                    )
                                ), 'ns1')
                            ), 'ns1'),

                            new XmlElement('FareBasisCode', array(),
                                new XmlElement('Code', array(), 'Empty', 'ns1')
                            , 'ns1')
                        ), 'ns1')
                    , 'ns1'),

                    new XmlElement('FlightSegmentList', array(),
                        HelperRequest::getListRequestParam('FlightSegment', $booking->getSegments())
                    , 'ns1'),

                    new XmlElement('OriginDestinationList', array(),
                        HelperRequest::buildRequestParam('OriginDestinationTicketing', $booking->getSegments())
                    , 'ns1'),

                    $InstructionsList,

                    new XmlElement('TermsList', array('ListKey' => 'TL1'),
                        HelperRequest::getListRequestParam('Term', $booking->getSegments())
                    , 'ns1')

                ), 'ns1')*/
            ), 'ns1')
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
        return 'AirDocIssueRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'demandTickets';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}