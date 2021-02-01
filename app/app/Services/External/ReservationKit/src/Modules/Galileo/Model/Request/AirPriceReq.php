<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Avia\Model\Helper\SearchRequestOrBookingChecker;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Component\XML\XmlElement;

class AirPriceReq extends Request
{
    protected $_xmlns_com  = 'http://www.travelport.com/schema/common_v40_0';
    protected $_xmlns_univ = 'http://www.travelport.com/schema/universal_v40_0';

    /**
     * @param \RK_Avia_Entity_Search_Request $searchRequest
     * @throws \RK_Core_Exception
     */
    public function __construct(\RK_Avia_Entity_Search_Request $searchRequest)
    {
        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        $this->addParam('AirItinerary',
            new XmlElement('AirItinerary', array(),
                HelperRequest::getListRequestParam('AirSegment', $searchRequest->getSegments()),
            'air')
        );

        $BrandModifiers = null;
        if (SearchRequestOrBookingChecker::isAdultOnly($searchRequest)) {
            $BrandModifiers = new XmlElement('BrandModifiers', array('ModifierType' => 'FareFamilyDisplay'), null, 'air');
        }

        $attributesAirPricingModifiers = [
            'CurrencyType' => Requisites::getInstance()->getCurrencyWAB()
        ];

        if (Requisites::getInstance()->getRules()->getSearchPCC() === '39NE') {
            $attributesAirPricingModifiers['InventoryRequestType'] = 'DirectAccess';
        }
        
		$passengers = HelperRequest::getSearchPassengerList($searchRequest->getPassengers());
		
		// маршрут TODO
        /*
		$route = array();
		$segments = $searchRequest->getSegments();
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
		*/
		
		
		//$agreements = \User::getParameter('[avia][custom_3d_agreements]');

		
		
        // Брендированые тарифы + трехсторонний договор
        $this->addParam('AirPricingModifiers',
            new XmlElement('AirPricingModifiers', $attributesAirPricingModifiers,
                array(
                    HelperRequest::buildRequestParam('AccountCodes', $searchRequest),
					//new XmlElement('ManualFareAdjustment', array('AdjustmentType' => 'Percentage', 'AppliedOn' => 'Base', 'PassengerRef' => $passengers[0]->getAttributes()['BookingTravelerRef'], 'Value' => '-2'), null, 'air'),
                    $BrandModifiers
                ),
            'air')
        );

		
		$this->addParam('SearchPassenger',
            new XmlElement('', null, $passengers)
        );

        $this->addParam('AirPricingCommand',
            new XmlElement('AirPricingCommand', array(),
                HelperRequest::getListRequestParam('AirSegmentPricingModifiers', $searchRequest->getSegments()),
            'air')
        );

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'xmlns:air'        => 'http://www.travelport.com/schema/air_v40_0',
            'AuthorizedBy'     => 'user',
            'TargetBranch'     => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getRules()->getSearchPCC()),
            'TraceId'          => 'trace'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_AirPriceReq;
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