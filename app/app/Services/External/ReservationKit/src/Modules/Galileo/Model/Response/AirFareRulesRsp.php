<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;

class AirFareRulesRsp extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->AirFareRulesRsp)) {
            $body = $this->getResponse()->Body->AirFareRulesRsp;
        } else {
            throw new \Exception('Bad AirFareRulesRsp response content');
        }

        $result = array();

        // Прайс
        $FareRule = $body->FareRule;

        $FareRules = array();
        foreach ($FareRule->FareRuleLong as $FareRuleLong) {
            $FareRules[(string) $FareRuleLong['Category']] = (string) $FareRuleLong;
        }

        $this->setResult($FareRules);

        return $this;
    }
}