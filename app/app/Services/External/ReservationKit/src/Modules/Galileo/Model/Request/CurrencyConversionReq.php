<?php

namespace ReservationKit\src\Modules\Galileo\Model\Request;

use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Component\XML\XmlElement;

class CurrencyConversionReq extends Request
{
    public function __construct($from, $to)
    {
        $this->addParam('BillingPointOfSaleInfo',
            new XmlElement('BillingPointOfSaleInfo', array('OriginApplication' => 'UAPI'), null, 'com')
        );

        $attributesCurrencyConversion = array(
            //'OriginalAmount' => '134.44',
            'From' => $from,
            'To'   => $to
        );

        $this->addParam('CurrencyConversion',
            new XmlElement('CurrencyConversion', $attributesCurrencyConversion, null, 'util')
        );

        parent::__construct();
    }

    public function getFunctionAttributes()
    {
        return array(
            'xmlns:util'       => 'http://www.travelport.com/schema/util_v40_0',
            'AuthorizedBy'     => 'user',
            'TargetBranch'     => Requisites::getInstance()->getBranchCode(Requisites::getInstance()->getRules()->getSearchPCC()),
            'TraceId'          => 'trace'
        );
    }

    public function getWSDLFunctionName()
    {
        return self::FUNCTION_CurrencyConversionReq;
    }

    public function getWSDLServiceName()
    {
        return self::SERVICE_CurrencyConversionService;
    }

    public function getFunctionNameSpace()
    {
        return 'util';
    }
}