<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class BaggageAllowanceInfo
{
    private $_origin;

    private $_destination;

    private $_carrier;

    private $_textInfo = array();

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->_origin;
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin)
    {
        $this->_origin = $origin;
    }

    /**
     * @return mixed
     */
    public function getDestination()
    {
        return $this->_destination;
    }

    /**
     * @param mixed $destination
     */
    public function setDestination($destination)
    {
        $this->_destination = $destination;
    }

    /**
     * @return mixed
     */
    public function getCarrier()
    {
        return $this->_carrier;
    }

    /**
     * @param mixed $carrier
     */
    public function setCarrier($carrier)
    {
        $this->_carrier = $carrier;
    }

    /**
     * @return array
     */
    public function getTextInfo()
    {
        return $this->_textInfo;
    }

    /**
     * Возвращает описание багажа по номеру описания
     *
     * @return array
     */
    public function getTextInfoByNum($num)
    {
        return isset($this->_textInfo[$num]) ? $this->_textInfo[$num] : null;
    }

    /**
     * @param array $textInfo
     */
    public function setTextInfo($textInfo)
    {
        $this->_textInfo = $textInfo;
    }

    /**
     * @param $text
     */
    public function addTextInfo($text)
    {
        $this->_textInfo[] = $text;
    }
}