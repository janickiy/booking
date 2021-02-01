<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Core\Model\Entity\CurrencyRates;
use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;

class CurrencyConversionParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->CurrencyConversionRsp)) {
            $body = $this->getResponse()->Body->CurrencyConversionRsp;
        } else {
            throw new \Exception('Bad CurrencyConversionRsp response content');
        }

        $currencyRates = new CurrencyRates();
        foreach ($body->CurrencyConversion as $currencyConversion) {
            $rate       = (string) $currencyConversion['BankSellingRate'];
            $currToCurr = (string) $currencyConversion['From'] . '/' . (string) $currencyConversion['To'];  // EUR/RUB

            $currencyRates->addRate($currToCurr, $rate);
        }

        return $currencyRates;
    }
}