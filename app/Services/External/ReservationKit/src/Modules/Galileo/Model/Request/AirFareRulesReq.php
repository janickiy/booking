<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Component\XML\XmlElement;

class AirFareRulesReq extends Request
{
    public function __construct($fareInfoRef, $keyRule)
    {
        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        $attributesFareRuleKey = array(
            'ProviderCode' => Requisites::PROVIDER_CODE,
            'FareInfoRef'  => $fareInfoRef
        );

        $this->addParam('FareRuleKey',
            new XmlElement('FareRuleKey', $attributesFareRuleKey, $keyRule, 'air')
        );

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'xmlns:air'    => 'http://www.travelport.com/schema/air_v40_0',
            //'xmlns:com'    => 'http://www.travelport.com/schema/common_v34_0',
            'TargetBranch' => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getRules()->getSearchPCC()),
            'LanguageCode' => Requisites::LANGUAGE_CODE_RU,
            //'AuthorizedBy' => 'user',
            'TraceId'      => 'trace',
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_AirFareRulesReq;
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