<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Component\XML\XmlElement;

class UniversalRecordRetrieveReq extends Request
{
    /**
     * @param Booking $booking
     */
    public function __construct(Booking $booking)
    {
        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        if ($booking->getLocatorUniversalRecord()) {
            // Чтение по локатору UR
            $this->addParam('UniversalRecordLocatorCode',
                new XmlElement('UniversalRecordLocatorCode', array(), $booking->getLocatorUniversalRecord(), 'univ')
            );
        } else if ($booking->getLocatorProviderReservation()) {
            // Чтение по локатору операторов
            $attributesProviderReservationInfo = array('ProviderCode' => '1G', 'ProviderLocatorCode' => $booking->getLocatorProviderReservation());
            $this->addParam('ProviderReservationInfo',
                new XmlElement('ProviderReservationInfo', $attributesProviderReservationInfo, null, 'univ')
            );
        }

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'AuthorizedBy' => 'user',
            'TargetBranch' => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getRules()->getSearchPCC()),
            'TraceId'      => 'trace'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_UniversalRecordRetrieveReq;
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