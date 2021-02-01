<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Component\XML\XmlElement;

class AirCreateReservationReq extends Request
{
    protected $_xmlns_com  = 'http://www.travelport.com/schema/common_v40_0';
    protected $_xmlns_univ = 'http://www.travelport.com/schema/universal_v40_0';

    public function __construct(\RK_Avia_Entity_Booking $booking)
    {
        /* @var Booking $booking */
        $attributesAirPricingSolution = array(
            'Key'                   => createBase64UUID(),
            'TotalPrice'            => $booking->getTotalPrice()->getAmount(),
            'BasePrice'             => $booking->getBasePrice()->getAmount(),
            'ApproximateTotalPrice' => $booking->getApproximateTotalPrice()->getAmount(),
            'ApproximateBasePrice'  => $booking->getApproximateBasePrice()->getAmount(),
            'Taxes'                 => $booking->getTaxes()->getAmount(),
            'ApproximateTaxes'      => $booking->getApproximateTaxes()->getAmount(),
            'QuoteDate'             => \RK_Core_Date::now()->formatTo(\RK_Core_Date::DATE_FORMAT_DB_DATE),
        );

        if ($booking->getEquivalentBasePrice() instanceof \RK_Core_Money) {
            $attributesAirPricingSolution['EquivalentBasePrice'] = $booking->getEquivalentBasePrice()->getAmount();
        }

        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        $isNeedMiddleName = $booking->getValidatingCompany() === 'SU' && $booking->isFlightInternal('RU') ? true : false;

        // FIXME
		$FOIDs = array();
		if (in_array($booking->getValidatingCompany(), array('KC','UT', 'U6'))) {
		    for ($i = 0; $i < count($booking->getSegments()); $i++) {
                $FOIDs[] = array('segment' => $i + 1, 'airline' => $booking->getValidatingCompany());
            }
        }

        // Пассажиры
        $passengers = HelperRequest::getListRequestParam('BookingTraveler', $booking->getPassengers(), array('need_middlename' => $isNeedMiddleName, 'foid' => $FOIDs, 'airline' => $booking->getValidatingCompany()));

		$this->addParam('BookingTraveler',
            new XmlElement('', array(),
                $passengers
            )
        );

        // Дополнительная сервисная информация (номера телефонов пассажиров)
        if ($booking->getValidatingCompany() !== 'TK') {
            $this->addParam('OSI',
                new XmlElement('', array(),
                    HelperRequest::getListRequestParam('OSI', $booking->getPassengers(), $booking->getValidatingCompany())
                )
            );
        }

        // Дополнительная сервисная информация (тур-код)
        if ($agreement = $booking->getTriPartyAgreementByNum(0)) {
            $attributesOSI = array(
                'Key' => createBase64UUID(),
                'Carrier' => $agreement->getCarrier(),
                'Text' => 'OIN '.$agreement->getTourCode()
            );

            $this->addParam('OSI2', new XmlElement('OSI', $attributesOSI, null, 'com'));
        }

        // Проверка целостности сегментов
        $this->addParam('ContinuityCheckOverride',
            new XmlElement('ContinuityCheckOverride', array('Key' => '1'), 'Yes','com')
        );

        // Контакты агентства
        $this->addParam('AgencyContactInfo',
            new XmlElement('AgencyContactInfo', array(),
                new XmlElement('PhoneNumber', array('Type' => 'Agency', 'Location' => 'MOW', 'Number' => '74956909563 TRIVAGO'), null, 'com'),
                'com')
        );

        // Форма оплаты
        if ($booking->getValidatingCompany() === 'SU' && in_array( Requisites::getInstance()->getRules()->getSearchPCC(), array('6UQ2', 'L8W') )) {
            $this->addParam('FormOfPayment',
                new XmlElement('FormOfPayment', array('Key' => createBase64UUID(), 'Type' => 'Credit'),
                    new XmlElement('CreditCard', array('CVV' => '427', 'ExpDate' => '2021-03', 'Key' => createBase64UUID(), 'Name' => 'Oleg Aronov', 'Number' => '375094669831000', 'Type' => 'AX'), null, 'com'),
                'com')
            );

        } else {
            $this->addParam('FormOfPayment',
                new XmlElement('FormOfPayment', array('Key' => createBase64UUID(), 'Type' => 'Cash'),
                    null,
                'com')
            );
        }


		// маршрут
		$route = array();
		$segments = $booking->getSegments();
		for ($s=0;$s<count($segments);$s++)
		{
			$c_out = \Avia::getCityCode($segments[$s]->getDepartureCode());
			$c_in = \Avia::getCityCode($segments[$s]->getArrivalCode());	
			if (!$s || !$segments[$s-1]->isNeedConnectionToNextSegment())
			{
				if (!$s) $route[] = $c_out;
				// несостыковка частей маршрута
				elseif ($c_out != \Avia::getCityCode($segments[$s-1]->getArrivalCode())) $route[] = '';
				else $route[] = $c_out;
			}
			if ($s == count($segments) - 1 || !$segments[$s]->isNeedConnectionToNextSegment()) $route[] = $c_in;
		}
		$route = implode('-', $route);
		
		$items = \User::getAgreements($route);
		
		$discount = null;
		for ($s=0;$s<count($segments);$s++)
		{
			$class = $segments[$s]->getBaseClass();
			for ($i=0;$i<count($items);$i++)
				if (in_array($segments[$s]->getSubClass(), $items[$i]['BISC']) && $items[$i]['percent_'.strtolower($class)] && $booking->getValidatingCompany() == $items[$i]['owner']) {$discount = $items[$i]['percent_'.strtolower($class)]; break 2;}
		}
		
		$discounts = array();
		if ($discount)
			for ($i=0;$i<count($passengers);$i++)
				$discounts[] = new XmlElement('ManualFareAdjustment', array('AdjustmentType' => 'Percentage', 'AppliedOn' => 'Base', 'PassengerRef' => $passengers[$i]->getData()[0]->getAttributes()['Key'], 'Value' => '-'.$discount), null, 'air');

        // Прайсы и сегменты
        $this->addParam('AirPricingSolution',
            new XmlElement('AirPricingSolution', $attributesAirPricingSolution, array_merge(
                HelperRequest::getListRequestParam('AirSegment', $segments),
                array(
                    HelperRequest::buildRequestParam('AirPricingInfo', $booking, $discounts)    // В прайсинг устанавливается keyRef
                ),
                HelperRequest::getListRequestParam('HostToken', $booking->getHostTokensForPrice())

            ), 'air')
        );

        $ticketTimeLimit = $booking->calculateTiсketTimelimit();
        $autoCancelTime  = strtoupper($ticketTimeLimit->getDateTime()->format('Hi')) . '/X';;

        $this->addParam('ActionStatus',
            new XmlElement('ActionStatus', array(
                'ProviderCode' => '1G',
                'TicketDate' => $ticketTimeLimit->getDateTime()->format(\RK_Core_Date::DATE_FORMAT_SERVICES), // $ticketTimeLimit,
                'Type' => 'TAU', // 'TTL'
                'AirportCode' => $booking->getSegment(0)->getDepartureCode(),
                'SupplierCode' => $booking->getSegment(0)->getOperationCarrierCode()),
                new XmlElement('Remark', null, $autoCancelTime, 'com'),
            'com')
        );


		//printr($this);
		//exit();
			
        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        // TODO добавить правила для бронирований и избавиться от этого условия здесь
        $ticketPCC = Requisites::getInstance()->getTicketPCC();
        if ($ticketPCC === '39NE') {
            $bookingPCC = '39NE';
        } else {
            $bookingPCC = Requisites::getInstance()->getRules()->getSearchPCC();
        }

        return array(
            'xmlns:air'          => 'http://www.travelport.com/schema/air_v40_0',
            'AuthorizedBy'       => 'user',
            'RetainReservation'  => 'Both',
            'TargetBranch'       => Requisites::getInstance()->getBranchCode($bookingPCC),
            'TraceId'            => 'trace'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_AirCreateReservationReq;
    }

    public function getWSDLServiceName()
    {
        return self::SERVICE_AirService;
    }

    public function getFunctionNameSpace()
    {
        return 'univ';
    }
}