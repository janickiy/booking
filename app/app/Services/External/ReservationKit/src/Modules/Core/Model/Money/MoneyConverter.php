<?php

namespace ReservationKit\src\Modules\Core\Model\Money;

/**
 * Конвертер валют
 */
class MoneyConverter
{
    /**
     * Курсы валют
     *
     * @var array
     */
    protected $_rates;

    /**
     * @var MoneyConverter
     */
    protected static $_instance;

    /**
     * @return MoneyConverter
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new MoneyConverter());
    }

    /**
     *
     * Выполняет конверсию валюты
     *
     * @param \RK_Core_Money $money
     * @param $currency
     * @param $rate
     * @return \RK_Core_Money
     * @throws \RK_Core_Exception
     */
    public function convert(\RK_Core_Money $money, $currency, $rate/* = null*/)
    {
        if ($money->getCurrency() === $currency) {
            return new \RK_Core_Money($money->getValue(), $currency);
        }

        if (empty($rate)) {
            /*
            $this->readRates();

            $rateFrom = $this->getRate($money->getCurrency());
            $rateTo = $this->getRate($currency);
            $value = ($rateTo / $rateFrom) * $money->getValue();
            */
        }

        $value = $rate * $money->getValue();

        return new \RK_Core_Money($value, $currency);
    }

    /**
     * Возвращает курсы валют
     *
     * @return array
     */
    public function getRates()
    {
        $this->readRates();

        return $this->_rates;
    }

    /**
     * Считывает курсы валют
     */
    protected function readRates()
    {
        // TODO актуальные курсы валют длжны браться из БД
    }

    /**
     * Возвращает курс для валюты
     *
     * @param string $currency
     * @return double
     * @throws \RK_Core_Exception
     */
    public function getRate($currency)
    {
        $this->readRates();

        if (isset($this->_rates[$currency])) {
            return $this->_rates[$currency];
        }

        throw new \RK_Core_Exception('Currency not found');
    }
}