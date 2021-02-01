<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity;

class FareInfo
{
    /**
     * Код тарифа
     *
     * @var string
     */
    private $_fareCode;

    // TODO перенести в Air_Price
    /**
     * Информация о багаже
     *
     * @var string
     */
    private $_baggageAllowance;

    /**
     * Код скидки пассажира
     *
     * @var string
     */
    private $_fareTicketDesignator;

    /**
     * Ключ для правил тарифа
     *
     * @var string
     */
    private $_fareRule;

    /**
     * Возвращает код тарифа
     *
     * @return string
     */
    public function getFareCode()
    {
        return $this->_fareCode;
    }

    /**
     * Устанавливает код тарифа
     *
     * @param string $fareCode
     */
    public function setFareCode($fareCode)
    {
        $this->_fareCode = $fareCode;
    }

    // TODO перенести в Air_Price
    /**
     * Возвращает информацию о багаже
     *
     * @return string
     */
    public function getBaggageAllowance()
    {
        return $this->_baggageAllowance;
    }

    /**
     * Устанавливает информацию о багаже
     *
     * @param string $baggageAllowance
     */
    public function setBaggageAllowance($baggageAllowance)
    {
        $this->_baggageAllowance = $baggageAllowance;
    }

    /**
     * Возвращает код скидки пассажира
     *
     * @return string
     */
    public function getFareTicketDesignator()
    {
        return $this->_fareTicketDesignator;
    }

    /**
     * Устанавливает код скидки пассажира
     *
     * @param string $fareTicketDesignator
     */
    public function setFareTicketDesignator($fareTicketDesignator)
    {
        $this->_fareTicketDesignator = $fareTicketDesignator;
    }

    /**
     * Возвращает ключ для правил тарифа
     *
     * @return string
     */
    public function getFareRule()
    {
        return $this->_fareRule;
    }

    /**
     * Устанавливает ключ для правил тарифа
     *
     * @param string $fareRule
     */
    public function setFareRule($fareRule)
    {
        $this->_fareRule = $fareRule;
    }
}