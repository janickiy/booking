<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

class OrderCancelParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if ($this->getResponse()->Body->OrderCancelRS->Success) {
            $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
            $this->getBooking()->setCancelDate(\RK_Core_Date::now());

        } else {
            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }
    }
}