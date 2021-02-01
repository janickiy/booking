<?php

namespace ReservationKit\src\Modules\Sirena\Model\Entity;

class Booking extends \RK_Avia_Entity_Booking
{
    public function __construct()
    {
        parent::__construct();

        $this->setSystem('Sirena');
    }
}