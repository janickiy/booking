<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request;

use ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param\AgentUserSender;

class AirDocVoidRQ extends Request
{
    /**
     * @param string $ticketNumber
     */
    public function __construct($ticketNumber)
    {
        $this->addParam('Document',
            new XmlElement('Document', array(),
                new XmlElement('Name', array(), '1.0', 'ns1'),
            'ns1')
        );

        $this->addParam('Party',
            new XmlElement('Party', array(),
                new XmlElement('Sender', array(),
                    new AgentUserSender(false, true, '0.52')
                , 'ns1')
            , 'ns1')
        );

        $this->addParam('Query',
            new XmlElement('Query', array(),
                array(
                    new XmlElement('TicketDocQuantity', array(), '1', 'ns1'),
                    new XmlElement('TicketDocument', array(),
                        array(
                            new XmlElement('TicketDocNbr', array(), (string) $ticketNumber, 'ns1'),
                            new XmlElement('Type', array(),
                                new XmlElement('Code', array(), '702', 'ns1')
                            , 'ns1'),
                        )
                    , 'ns1'),
                )
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
        return 'AirDocVoidRQ';
    }

    public function getWSDLFunctionName()
    {
        return 'voidTicket';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}