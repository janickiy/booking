<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Entity;

class Passenger extends \RK_Avia_Entity_Passenger
{
    /**
     * Порядковый номер пассажира в брони
     *
     * @var int
     */
    private $_RPH;

    /**
     * Возвращает порядковый номер пассажира в брони
     *
     * @return int
     */
    public function getRPH()
    {
        return $this->_RPH;
    }

    /**
     * Устанавливает порядковый номер пассажира в брони
     *
     * @param int $RPH
     */
    public function setRPH($RPH)
    {
        $this->_RPH = $RPH;
    }
}