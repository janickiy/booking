<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Entity;

use ReservationKit\src\Modules\Avia\Model\Entity\BaggageAllowance as AviaBaggageAllowance;

/**
 * Класс содержит иформацию специфическую (необходимую) для S7Agent
 */
class BaggageAllowance extends AviaBaggageAllowance
{
    /**
     * @var string
     */
    private $_key;

    /**
     * @var string
     */
    private $_baggageValue;

    /**
     * @var array
     */
    private $_descriptions = array();

    /**
     * Возвращает ключ для значения багажа
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Устанавливает ключ для значения багажа
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * Возвращает значение багажа
     *
     * @return string
     */
    public function getBaggageValue()
    {
        return $this->_baggageValue;
    }

    /**
     * Устанавливает значение багажа
     *
     * @param string $baggageValue
     */
    public function setBaggageValue($baggageValue)
    {
        $this->_baggageValue = $baggageValue;
    }

    /**
     * @return array
     */
    public function getDescriptions()
    {
        return $this->_descriptions;
    }

    /**
     * @param array $descriptions
     */
    public function setDescriptions($descriptions)
    {
        $this->_descriptions = $descriptions;
    }

    /**
     * @param mixed $description
     */
    public function addDescription($description)
    {
        $this->_descriptions[] = $description;
    }
}