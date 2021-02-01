<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity;

/**
 * Класс описывающий 3х стороний договор
 */
class TriPartyAgreement
{
    /**
     * Тур-код
     *
     * @var string
     */
    private $_tourCode;

    /**
     * Аккаунт код
     *
     * @var string
     */
    private $_accountCode;

    /**
     * Размер скидки (в процентах)
     *
     * @var float
     */
    private $_discount;

    /**
     * Код перевозчика
     *
     * @var string
     */
    private $_carrier;

    public function __construct($tourCode = null, $accountCode = null, $discount = null, $carrier = null)
    {
        $this->setTourCode($tourCode);
        $this->setAccountCode($accountCode);
        $this->setDiscount($discount);
        $this->setCarrier($carrier);
    }

    /**
     * @return string
     */
    public function getTourCode()
    {
        return $this->_tourCode;
    }

    /**
     * @param string $tourCode
     */
    public function setTourCode($tourCode)
    {
        $this->_tourCode = $tourCode;
    }

    /**
     * @return string
     */
    public function getAccountCode()
    {
        return $this->_accountCode;
    }

    /**
     * @param string $accountCode
     */
    public function setAccountCode($accountCode)
    {
        $this->_accountCode = $accountCode;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->_discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount)
    {
        $this->_discount = $discount;
    }

    /**
     * @return string
     */
    public function getCarrier()
    {
        return $this->_carrier;
    }

    /**
     * @param string $carrier
     */
    public function setCarrier($carrier)
    {
        $this->_carrier = $carrier;
    }
}