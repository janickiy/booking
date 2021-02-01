<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class OrderCancelRQ extends Request
{
    /**
     * @param \RK_Avia_Entity_Booking $booking
     */
    public function __construct(\RK_Avia_Entity_Booking $booking)
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

        $this->addParam('Query',
            new XmlElement('Query', array(),
                new XmlElement('BookingReferences', array(),
                    new XmlElement('BookingReference', array(), array(
                        new XmlElement('ID', array(), $booking->getLocator(), 'ns1'),
                        new XmlElement('AirlineID', array(), 'S7', 'ns1'),
                    ), 'ns1')
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
        return 'OrderCancelRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'cancelBooking';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}