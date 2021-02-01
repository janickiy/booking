<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class BookingInfo
{
    private $_bookingCode;

    private $_cabinClass;

    private $_fareInfoRef;

    private $_segmentRef;

    private $_hostTokenRef;

    /**
     * @return mixed
     */
    public function getBookingCode()
    {
        return $this->_bookingCode;
    }

    /**
     * @param mixed $bookingCode
     */
    public function setBookingCode($bookingCode)
    {
        $this->_bookingCode = $bookingCode;
    }

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
    public function getFareInfoRef()
    {
        return $this->_fareInfoRef;
    }

    /**
     * @param mixed $fareInfoRef
     */
    public function setFareInfoRef($fareInfoRef)
    {
        $this->_fareInfoRef = $fareInfoRef;
    }

    /**
     * @return mixed
     */
    public function getSegmentRef()
    {
        return $this->_segmentRef;
    }

    /**
     * @param mixed $segmentRef
     */
    public function setSegmentRef($segmentRef)
    {
        $this->_segmentRef = $segmentRef;
    }

    /**
     * @return mixed
     */
    public function getHostTokenRef()
    {
        return $this->_hostTokenRef;
    }

    /**
     * @param mixed $hostTokenRef
     */
    public function setHostTokenRef($hostTokenRef)
    {
        $this->_hostTokenRef = $hostTokenRef;
    }
}