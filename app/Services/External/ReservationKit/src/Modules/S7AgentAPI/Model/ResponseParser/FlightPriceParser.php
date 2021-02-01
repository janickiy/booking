<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

class FlightPriceParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if ($this->getResponse()->Body->FlightPriceRS->Success) {
            $response = $this->getResponse()->Body->FlightPriceRS;

            $fareRulesList = array();

            // Парсинг списка правил
            foreach ($response->DataLists->DisclosureList->Disclosures->Description as $Description) {
                $ObjectKey = str_replace('FR1CAT', '', (string) $Description['ObjectKey']);
                $fareRulesList[$ObjectKey] = (string) $Description->Text;
            }

            $this->setResult($fareRulesList);

            return $this->getResult();

        } else {
            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }
    }
}