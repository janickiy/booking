<?php

namespace ReservationKit\src\Modules\Sirena\Model\Interfaces;

interface IRequest
{
    public function getRequestName();

    public function getRequestAttributes();
}