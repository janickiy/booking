<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Entity\SearchRequest as GalileoSearchRequest;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Component\XML\XmlElement;

class LowFareSearchReq extends Request
{
    /**
     * Классы:
     * - Economy
     * - PremiumEconomy
     * - Business
     * - First
     * - PremiumFirst
     * - Upper - applies to PreferredCabin only (available with Air v35.0) Release 15.5
     *
     * @param GalileoSearchRequest $searchRequest
     */
    public function __construct(/*GalileoSearchRequest*/ \RK_Avia_Entity_Search_Request $searchRequest)
    {
        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        // Сегменты
        $this->addParam('SearchAirLeg',
            new XmlElement(null, array(), HelperRequest::getListRequestParam('SearchAirLeg', $searchRequest->getSegments()))
        );

        // Только прямые рейсы
        $directXmlElement = null;
        if ($searchRequest->isDirect()) {
            $directXmlElement = new XmlElement('FlightType', array('NonStopDirects' => 'true'), null, 'air');
        }

        // Модификаторы поиска
        $this->addParam('AirSearchModifiers',
            new XmlElement('AirSearchModifiers', array('MaxSolutions' => '400'), array(
                new XmlElement('PreferredProviders', array(),
                    new XmlElement('Provider', array('Code' => '1G'), null, 'com'),
                'air'),

                // Разрешенные авиакомпании
                HelperRequest::getFilterByCarriers('PermittedCarriers', $searchRequest->getCarriers()),

                // Запрещенные авиакомпании
                HelperRequest::getFilterByCarriers('ProhibitedCarriers', $searchRequest->getProhibitedCarriers()),

                // Только прямые рейсы
                $directXmlElement
            ),
            'air')
        );


		$passengers = HelperRequest::getSearchPassengerList($searchRequest->getPassengers());
		
        // Пассажиры
        $this->addParam('SearchPassenger',
            new XmlElement(null, array(), $passengers)
        );

        // Модификатор прайса + трехсторонний договор
        $this->addParam('AirPricingModifiers',
            new XmlElement('AirPricingModifiers', array('InventoryRequestType' => 'DirectAccess', 'CurrencyType' => 'RUB'),
                array
				(
					HelperRequest::buildRequestParam('AccountCodes', $searchRequest),
					//new XmlElement('ManualFareAdjustment', array('AdjustmentType' => 'Percentage', 'AppliedOn' => 'Base', 'PassengerRef' => $passengers[0]->getAttributest()['BookingTravelerRef'], 'Value' => '-2'), null, 'air')
				),
            'air')
        );

        $this->addOption('remote_ip', $searchRequest->getOptionByKey('remote_ip'));
        
        parent::__construct();
    }

    /**
     * SolutionResult => true, вернет AirPricingSolution
     * SolutionResult => false, вернет AirPricePointList
     *
     * @return array
     * @throws \RK_Core_Exception
     */
    public function getFunctionAttributes()
    {
        return array(
            'xmlns:air'               => 'http://www.travelport.com/schema/air_v40_0',
            'TargetBranch'            => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getRules()->getSearchPCC()),
            'AuthorizedBy'            => 'user',
            'SolutionResult'          => 'true',
            'TraceId'                 => 'trace'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_LowFareSearchReq;
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