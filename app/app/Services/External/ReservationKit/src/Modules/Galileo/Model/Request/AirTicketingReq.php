<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Component\XML\XmlElement;

class AirTicketingReq extends Request
{
    /**
     * @param Booking $booking
     */
    public function __construct(Booking $booking)
    {
        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        $this->addParam('AirReservationLocatorCode',
            new XmlElement('AirReservationLocatorCode', array(), $booking->getLocatorAirReservation(), 'air')
        );

        $ListAirPricingInfoRef = array();
        foreach ($booking->getAirPricingInfoRef() as $priceKeyRef) {
            $ListAirPricingInfoRef[] = new XmlElement('AirPricingInfoRef', array('Key' => $priceKeyRef), null, 'air');
        }

        $this->addParam('AirPricingInfoRef',
            new XmlElement(null, array(), $ListAirPricingInfoRef)
        );

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'xmlns:air'        => 'http://www.travelport.com/schema/air_v40_0',
            'AuthorizedBy'     => 'user',
            'BulkTicket'       => 'false',
            'ReturnInfoOnFail' => 'true',
            'TargetBranch'     => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getTicketPCC()),
            'TraceId'          => 'trace'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_AirTicketingReq;
    }

    public function getWSDLServiceName()
    {
        return self::SERVICE_AirService;
    }

    public function getFunctionNameSpace()
    {
        return 'air';
    }
}