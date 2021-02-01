<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Enum\ModifyEnum;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;

class UniversalRecordModifyReq extends Request
{
    protected $_xmlns_com  = 'http://www.travelport.com/schema/common_v40_0';
    protected $_xmlns_univ = 'http://www.travelport.com/schema/universal_v40_0';

    private $_booking;

    /**
     * @param GalileoBooking $booking
     * @param ModifyEnum $modifyMethod
     */
    public function __construct(GalileoBooking $booking, ModifyEnum $modifyMethod)
    {
        $this->_booking = $booking;

        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        $attributesRecordIdentifier = array(
            'ProviderCode'         => '1G',
            'ProviderLocatorCode'  => $booking->getLocatorProviderReservation(),
            'UniversalLocatorCode' => $booking->getLocatorUniversalRecord()
        );

        $this->addParam('RecordIdentifier',
            new XmlElement('RecordIdentifier', $attributesRecordIdentifier, null, 'univ')
        );

        // Параметры модификации
        $this->{$modifyMethod->getValue()}($booking);

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        // TODO добавить правила для бронирований и избавиться от этого условия здесь
        if (0 && Requisites::getInstance()->getRules()->getSearchPCC() === 'L8W') {
            $modifyPCC = '6UQ2';
        } else {
            $modifyPCC = Requisites::getInstance()->getRules()->getSearchPCC();
        }

        return array(
            'xmlns:air'    => 'http://www.travelport.com/schema/air_v40_0',
            'AuthorizedBy' => 'user',
            'ReturnRecord' => 'true',
            'TargetBranch' => Requisites::getInstance()->getBranchCode($modifyPCC),
            'Version'      => $this->_booking->getVersion()
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_UniversalRecordModifyReq;
    }

    public function getWSDLServiceName()
    {
        return self::SERVICE_UniversalRecord;
    }

    public function getFunctionNameSpace()
    {
        return 'univ';
    }

    private function updatePrices(GalileoBooking $booking)
    {
        // Удаление прайсов
        $airPricingInfoRef = $booking->getAirPricingInfoRef();
        if (!empty($airPricingInfoRef)) {
            foreach ($booking->getAirPricingInfoRef() as $numAirPricingInfoRef => $AirPricingInfoRef) {
                $this->addParam('UniversalModifyCmd' . $numAirPricingInfoRef,
                    new XmlElement('UniversalModifyCmd', array('Key' => createBase64UUID()),
                        new XmlElement('AirDelete', array('Key' => $AirPricingInfoRef, 'Element' => 'AirPricingInfo', 'ReservationLocatorCode' => $booking->getLocatorAirReservation()), null, 'univ'),
                    'univ')
                );
            }
        }

        // Добавление новых прайсов
        $this->addParam('UniversalModifyCmd-add-price', HelperRequest::buildRequestParam('AddAirPricingInfo', $booking));
    }

    private function addCommission(GalileoBooking $booking)
    {
        // Удаление модификаторов прайса
        $passengers = $booking->getPassengers();
        if (is_array($passengers)) {
            foreach ($passengers as $keyPassenger => $passenger) {
                $this->addParam('UniversalModifyCmd' . $keyPassenger,
                    new XmlElement('UniversalModifyCmd', array('Key' => createBase64UUID()),
                        new XmlElement('AirDelete', array('Key' => $passenger->getTicketModifiersRef(), 'Element' => 'TicketingModifiers', 'ReservationLocatorCode' => $booking->getLocatorAirReservation()), null, 'univ'),
                    'univ')
                );
            }
        }

        $this->addParam('UniversalModifyCmd',
            new XmlElement('', array(),
                HelperRequest::buildRequestParam('AirPricingTicketingModifiers', $booking),
            'univ')
        );
    }

    private function addRemarks(GalileoBooking $booking)
    {
        // Список ремарок
        if ($booking->getRemarks()) {
            foreach ($booking->getRemarks() as $key => $remark) {

                if (!empty($remark)) {

                    $this->addParam('UniversalModifyCmd',
                        new XmlElement('UniversalModifyCmd', array('Key' => createBase64UUID()),
                            new XmlElement('UniversalAdd', array('ReservationLocatorCode' => $booking->getLocatorAirReservation()),
                                new XmlElement('GeneralRemark', array(),
                                    new XmlElement('RemarkData', array(), $remark, 'com'),
                                'com'),
                            'univ'),
                        'univ')
                    );
                    
                }

            }
        }
    }
}