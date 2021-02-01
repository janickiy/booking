<?php

namespace ReservationKit\src\Modules\Sirena\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;

use ReservationKit\src\Modules\Sirena\Model\Request;

class Order extends Request
{
    /**
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     */
    public function __construct($searchRequestOrBooking)
    {
        $this->addParam('Document',
            new XmlElement('Document', array(), null, 'ns1', true)
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
        return '';
    }

    public function getWSDLFunctionName()
    {
        return '';
    }

    public function getFunctionNameSpace()
    {
        return '';
    }
}