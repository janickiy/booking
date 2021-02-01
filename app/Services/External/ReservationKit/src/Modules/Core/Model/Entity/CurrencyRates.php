<?php

namespace ReservationKit\src\Modules\Core\Model\Entity;

class CurrencyRates
{
    private $_rates = array();

    /**
     * @return array
     */
    public function getRates()
    {
        return $this->_rates;
    }

    /**
     * @param array $rates
     */
    public function setRates($rates)
    {
        $this->_rates = $rates;
    }

    /**
     * @param string $currToCurr EUR/USD
     * @return float
     * @throws \RK_Core_Exception
     */
    public function getRate($currToCurr)
    {
        if (isset($this->_rates[$currToCurr])) {
            return (float) $this->_rates[$currToCurr];
        }

        throw new \RK_Core_Exception('Currency rate ' . $currToCurr . ' not found');
    }

    public function addRate($currToCurr, $value)
    {
        $this->_rates[$currToCurr] = $value;
    }
}