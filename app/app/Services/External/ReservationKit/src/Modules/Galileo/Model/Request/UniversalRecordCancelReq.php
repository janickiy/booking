<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Component\XML\XmlElement;

class UniversalRecordCancelReq extends Request
{
    private $_PNR;

    /**
     * @param \RK_Avia_Entity_Booking|GalileoBooking $booking
     */
    public function __construct(\RK_Avia_Entity_Booking $booking)
    {
        $this->setPNR($booking->getLocatorUniversalRecord());

        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
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
            'TargetBranch'               => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getRules()->getSearchPCC()),
            'TraceId'                    => 'trace',
            'UniversalRecordLocatorCode' => $this->getPNR(),
            'Version'                    => '1'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_UniversalRecordCancelReq;
    }

    public function getWSDLServiceName()
    {
        return self::SERVICE_UniversalRecord;
    }

    public function getFunctionNameSpace()
    {
        return 'univ';
    }
}