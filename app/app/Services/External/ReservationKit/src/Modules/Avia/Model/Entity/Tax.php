<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity;

/**
 * Класс описывающий таксу
 */
class Tax
{
    /**
     * Код таксы
     *
     * @var string
     */
    private $_code;

    /**
     * Стоимость таксы
     *
     * @var \RK_Core_Money
     */
    private $_amount;

    public function __construct(string $code, \RK_Core_Money $amount)
    {
        $this->setCode($code);
        $this->setAmount($amount);

        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->_code = $code;
    }

    /**
     * @return \RK_Core_Money
     */
    public function getAmount(): \RK_Core_Money
    {
        return $this->_amount;
    }

    /**
     * @param \RK_Core_Money $amount
     */
    public function setAmount(\RK_Core_Money $amount)
    {
        $this->_amount = $amount;
    }
}