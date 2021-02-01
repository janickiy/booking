<?php

namespace ReservationKit\src\Modules\Galileo\Model;

use ReservationKit\src\Modules\Avia\Model\AbstractServiceFactory;
use ReservationKit\src\Modules\Galileo\Model\Service\AirService;

class Factory extends AbstractServiceFactory
{
    // TODO переименовать, то что ниже удалить
    public function getSearchService()
    {
        Requisites::getInstance();
        return new AirService();
    }

    // TODO этот метод более общий и он должен заменить метод getSearchService(). getSearchService - удалить
    public function getAviaService()
    {
        Requisites::getInstance();
        return new AirService();
    }

    public function getBookingService()
    {
        Requisites::getInstance($this->getPackage()->getBooking()->getData());
        //return new RK_Galileo_Service_Booking();
    }

    public function getTicketingService()
    {
        Requisites::getInstance($this->getPackage()->getTicketing()->getData());
        //return new RK_Galileo_Service_Ticketing();
    }

    public function getUpdateService()
    {
        Requisites::getInstance($this->getPackage()->getBooking()->getData());
        //return new RK_Galileo_Service_Update();
    }

    public function getRequisites()
    {
        return Requisites::getInstance();
    }
}