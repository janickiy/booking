<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Component\XML\XmlElement;

class GdsQueuePlaceReq extends Request
{
    private $_PNR;

    /**
     * @param \RK_Avia_Entity_Booking|GalileoBooking $booking
     */
    public function __construct(\RK_Avia_Entity_Booking $booking)
    {
        $this->setPNR($booking->getLocatorProviderReservation());

        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        $this->addParam('QueueSelector',
            new XmlElement('QueueSelector', array('Queue' => '17'), null, 'com')
        );

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getPNR()
    {
        return $this->_PNR;
    }

    /**
     * @param string $PNR
     */
    public function setPNR($PNR)
    {
        $this->_PNR = $PNR;
    }

    public function getFunctionAttributes()
    {
        return array(
            'AuthorizedBy'               => 'user',
            //'TargetBranch'               => Requisites::getInstance()->getBranchCode('36WB'),
            //'TraceId'                    => 'trace',
            //'UniversalRecordLocatorCode' => $this->getPNR(),
            //'Version'                    => '1',

            //'AuthorizedBy' => 'UAPI',
            'PseudoCityCode' => '36WB',
            'ProviderCode' => '1G',
            'ProviderLocatorCode' => $this->getPNR(),
            //'xmlns' => 'http://www.travelport.com/schema/gdsQueue_v40_0'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_GdsQueuePlaceReq;
    }

    public function getWSDLServiceName()
    {
        return self::SERVICE_GdsQueueService;
    }

    public function getFunctionNameSpace()
    {
        return 'univ';
    }
}