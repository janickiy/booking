<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking;
use ReservationKit\src\Modules\Galileo\Model\Entity\SearchRequest as GalileoSearchRequest;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Component\XML\XmlElement;

class AvailabilitySearchReq extends Request
{
    /**
     * @param GalileoSearchRequest $searchRequest
     */
    public function __construct(GalileoSearchRequest $searchRequest)
    {
        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        // Ссылка на следующие результаты поиска
        if (!empty($searchRequest->getNextResultReference())) {
            $this->addParam('NextResultReference',
                new XmlElement('NextResultReference', array('ProviderCode' => '1G'), $searchRequest->getNextResultReference(), 'com')
            );
        }

        // Сегменты
        $this->addParam('SearchAirLeg',
            new XmlElement(null, array(),
                HelperRequest::getListRequestParam('SearchAirLeg', $searchRequest->getSegments())
            )
        );

        // Запрещенные авиакомпании
        /*
        $prohibitedCarriers = null;
        $carriers = $searchRequest->getCarriers();
        if (empty($carriers) || in_array('UT', $carriers) || in_array('S7', $carriers)) {   // TODO Тупое услове. Переделать
            $prohibitedCarriers = new XmlElement('ProhibitedCarriers', array(), array(
                new XmlElement('Carrier', array('Code' => 'UT'), null, 'com'),
                new XmlElement('Carrier', array('Code' => 'S7'), null, 'com')
            ), 'air');
        }
        */

        // Только прямые рейсы
        $directXmlElement = null;
        if ($searchRequest->isDirect()) {
            $directXmlElement = new XmlElement('FlightType', array('NonStopDirects' => 'true'), null, 'air');
        }

        // Модификаторы поиска
        $this->addParam('AirSearchModifiers',
            new XmlElement('AirSearchModifiers', array('MaxSolutions' => '300'), array(
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

        $this->addOption('remote_ip', $searchRequest->getOptionByKey('remote_ip'));

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'xmlns:air'    => 'http://www.travelport.com/schema/air_v40_0',
            'TargetBranch' => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getRules()->getSearchPCC()),
            'AuthorizedBy' => 'user',
            'TraceId'      => 'trace'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_AvailabilitySearchReq;
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