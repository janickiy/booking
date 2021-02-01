<?php

namespace ReservationKit\src\Modules\Core\Model\Money;

class MoneyHelper
{
    /**
     * Разбивает строку со стоимость на цену и валюту
     *
     * @param $string
     * @return null|\RK_Core_Money
     */
    static public function parseMoneyString($string)
    {
        // Например, RUB4500 или EUR4.44
        if (preg_match('/([A-Z]{3})([0-9]{1,}.?[0-9]{0,2})/i', $string, $matches)) {
            return new \RK_Core_Money($matches[2], $matches[1]);
        }

        return null;
    }
}