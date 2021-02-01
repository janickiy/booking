<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Entity;

class Booking extends \RK_Avia_Entity_Booking
{
    public function __construct()
    {
        parent::__construct();

        $this->setSystem('S7Agent');
    }
}