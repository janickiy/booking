<?php

use ReservationKit\src\Modules\Core\Model\Money\MoneyConverter;

/**
 * Объект денег
 */
class RK_Core_Money
{
    /**
     * Значение
     *
     * @var float
     */
    protected $_value;

    /**
     * Валюта
     *
     * @var string
     */
    protected $_currency;

    /**
     * Создает новый экземпляр денег
     * TODO дикикий параметр $default удалить
     * @param float $value
     * @param string $currency
     */
    public function __construct($value = null, $currency = null, $default = 'RUB')
    {
        if (isset($value, $currency)) {
            $this->_value = (double) $value;
            $this->_currency = (string) $currency;

        } else if ($value instanceof RK_Core_Money) {
            $this->_value = $value->getValue();
            $this->_currency = $value->getCurrency();

        } else {
            $this->_value = 0.0;
            $this->_currency = isset($currency) ? $currency : $default;
        }
    }

    /**
     * Возвращает значение
     *
     * @param int $round порядок округления
     * @return double
     */
    public function getValue($round = null)
    {
        return isset($round) ? round($this->_value, $round) : $this->_value;
    }

    /**
     * Возвращает валюту
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    public function getAmount($format = 'CURVAL')
    {
        $search = array(
            'CUR' => $this->getCurrency(),
            'VAL' => $this->getValue()
        );

        if ($this->getCurrency() !== 'RUB') {
            $search['VAL'] = number_format($search['VAL'], 2, '.', '');
        }

        return str_replace(array_keys($search), array_values($search), $format);
    }

    /**
     * Возвращает строковое представление обьекта
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getValue(2) . ' ' . $this->getCurrency();
    }

    /**
     * Выполняет конвертирование в другую валюту
     *
     * @param $currency
     * @param $rate
     * @return RK_Core_Money
     */
    public function convert($currency, $rate)
    {
        return MoneyConverter::getInstance()->convert($this, $currency, $rate);
    }

    /**
     * Выполняет сложение
     *
     * @param RK_Core_Money $money
     * @return RK_Core_Money
     * @throws RK_Core_Exception
     */
    public function add(RK_Core_Money $money)
    {
        // FIXME По умолчанию RK_Core_Money создается в валюте RUB. Это приводит к таким фиксам ошибок. Придумать как этого избежать
        if ($this->_value === 0.0 || $this->_value === 0 || $this->_value === null) {
            $this->_currency = $money->getCurrency();
        }

        // Если прибавляется 0, то валюта берется как у текущего объекта
        if ($money->getValue() === 0.0) {
            $money = new \RK_Core_Money((float) 0, $this->_currency);
        }

        if ($money->getCurrency() !== $this->getCurrency()) {
            //$money = $money->convert($this->getCurrency()); TODO доделать конвертирование
            throw new RK_Core_Exception('RK_Core_money: Different currency: ' . $money->getCurrency() . ' - ' .  $this->getCurrency());
        }

        // FIXME должно возвращаться $this
        return new RK_Core_Money($this->getValue() + $money->getValue(), $this->getCurrency());
    }

    /**
     * Выполняет вычитание
     *
     * @param RK_Core_Money $money
     * @return RK_Core_Money
     * @throws RK_Core_Exception
     */
    public function sub(RK_Core_Money $money)
    {
        if ($money->getCurrency() !== $this->getCurrency()) {
            //$money = $money->convert($this->getCurrency()); TODO доделать конвертирование
            throw new RK_Core_Exception('Different currency');
        }
        return new RK_Core_Money($this->getValue() - $money->getValue(), $this->getCurrency());
    }

    /**
     * Выполняет умножение на ножитель $n
     *
     * @param int $n Множитель
     * @return RK_Core_Money
     */
    public function mult($n)
    {
        return new RK_Core_Money($this->getValue() * $n, $this->getCurrency());
    }

    /**
     * Выполняет деление на $n
     *
     * @param int $n Множитель
     * @return RK_Core_Money
     * @throws RK_Core_Exception
     */
    public function div($n)
    {
        if ($n == 0) {
            throw new RK_Core_Exception('Divide by zero');
        }
        return new RK_Core_Money($this->getValue() / $n, $this->getCurrency());
    }

    /**
     * Проверяет, с учетом курса, если $this СТРОГО БОЛЬШЕ $money
     *
     * @param RK_Core_Money $money
     * @return bool
     * @throws RK_Core_Exception
     */
    public function isGreater(RK_Core_Money $money)
    {
        if ($money->getCurrency() !== $this->getCurrency()) {
            //$money = $money->convert($this->getCurrency()); TODO доделать конвертирование
            throw new RK_Core_Exception('Different currency');
        }
        return $this->getValue() > $money->getValue();
    }

    /**
     * Проверяет, с учетом курса, если $this МЕНЬШЕ ИЛИ РАВНО $money
     *
     * @param RK_Core_Money $money
     * @return bool
     * @throws RK_Core_Exception
     */
    public function isLess(RK_Core_Money $money)
    {
        if ($money->getCurrency() !== $this->getCurrency()) {
            //$money = $money->convert($this->getCurrency()); TODO доделать конвертирование
            throw new RK_Core_Exception('Different currency');
        }
        return $this->getValue() <= $money->getValue();
    }

    /**
     * Проверяет значение на равенство
     *
     * @param RK_Core_Money $money
     * @return bool
     * @throws RK_Core_Exception
     */
    public function isEqual(RK_Core_Money $money)
    {
        if ($money->getCurrency() !== $this->getCurrency()) {
            //$money = $money->convert($this->getCurrency()); TODO доделать конвертирование
            throw new RK_Core_Exception('Different currency');
        }

        return abs($this->getValue(4) - $money->getValue(4)) < 1e-4;
    }

    /**
     * Округляет в большую сторону до ближайшего числа кратного $even
     *
     * @param int $even
     * @return $this
     */
    public function roundEvenUp($even = 1)
    {
        if (is_numeric($even) && $even > 0) {
            $this->_value = ceil($this->getValue() / $even) * $even;
        }

        return $this;
    }
}