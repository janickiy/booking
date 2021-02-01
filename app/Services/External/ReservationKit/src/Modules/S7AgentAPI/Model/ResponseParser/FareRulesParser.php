<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

class FareRulesParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if ($this->getResponse()->Body->FareRulesRS->Success) {
            $body = $this->getResponse()->Body->FareRulesRS;

        } else {
            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }

        // Прайс
        $Rules = $body->Rules;

        $FareRules = array();
        foreach ($Rules->Rule as $Rule) {
            $FareRules[str_replace('CAT', '', (string) $Rule->FareRuleCategory->Code)] = (string) $Rule->Text;
        }

        $this->setResult($FareRules);

        return $this;
    }
}