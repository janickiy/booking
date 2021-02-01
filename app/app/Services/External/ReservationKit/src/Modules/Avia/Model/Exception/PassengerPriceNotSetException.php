<?php

namespace ReservationKit\src\Modules\Avia\Model\Exception;

use ReservationKit\src\Modules\Avia\Model\AviaException;

/**
 * Исключение для отсутвия прайса у типа пассажира
 */
class PassengerPriceNotSetException extends AviaException
{
    protected $message = '';
}