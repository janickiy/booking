<?php

namespace ReservationKit\src\Modules\Galileo\Model\Abstracts;

use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Interfaces\IResponse;

abstract class Response implements IResponse
{
    /**
     * Список сообщений об ошибках
     *
     * @var array
     */
    private $_listErrorMessage = array();
    /**
     * @var \RK_Avia_Entity_Booking
     */
    private $_booking;

    protected $_response;

    /**
     * @var \RK_Avia_Entity_Booking[]
     */
    private $_result = array();

    /**
     * Объект брони
     *
     * @return \RK_Avia_Entity_Booking|GalileoBooking
     */
    public function getBooking()
    {
        return $this->_booking;
    }

    public function setBooking(\RK_Avia_Entity_Booking $booking = null)
    {
        if (is_null($booking)) {
            $booking = new \RK_Avia_Entity_Booking();
        }

        $this->_booking = $booking;
    }

    /**
     * Ответ
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->_response;
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->_result = $result;
    }

    /**
     * @return array
     */
    public function getListErrorMessage()
    {
        return $this->_listErrorMessage;
    }

    /**
     * @param array $listErrorMessage
     */
    public function setListErrorMessage($listErrorMessage)
    {
        $this->_listErrorMessage = $listErrorMessage;
    }

    public function addErrorMessage($message, $code = null)
    {
        $this->_listErrorMessage[] = $code ? $code . ': ' . $message : $message;
    }
}