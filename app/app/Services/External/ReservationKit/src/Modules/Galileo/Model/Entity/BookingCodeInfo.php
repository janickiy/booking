<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class BookingCodeInfo
{
    private $_cabinClass;

    private $_bookingCounts;

    /**
     * @return mixed
     */
    public function getCabinClass()
    {
        return $this->_cabinClass;
    }

    /**
     * @param mixed $cabinClass
     */
    public function setCabinClass($cabinClass)
    {
        $this->_cabinClass = $cabinClass;
    }

    /**
     * @return mixed
     */
    public function getBookingCounts()
    {
        return $this->_bookingCounts;
    }

    /**
     * @param mixed $bookingCounts
     */
    public function setBookingCounts($bookingCounts)
    {
        $this->_bookingCounts = $bookingCounts;
    }
}