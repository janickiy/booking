<?php

class RK_Sirena_Response_BookingCancel extends RK_Sirena_Response
{

    /**
     * @var RK_Avia_Entity_Booking
     */

    protected $_booking	= null;

    /**
     * @param RK_Avia_Entity_Booking $booking
     */

    function __construct(RK_Avia_Entity_Booking $booking) {
        $this->_booking = $booking;
    }

    /**
     * Парсер ответа
     * Ответ содержит элемент <ok> без содержимого
     *
     * @throws RK_Sirena_Exception
     */
    public function parse()
    {
        if (!$this->_responseContent) {
            throw new RK_Sirena_Exception('Bad Response Content in Booking Cancel');
        }

        // Сервис ответа
        if (!isset($this->_responseContent->answer)) {
            throw new RK_Sirena_Exception('Bad Booking-cancel response content');
        }

        if (isset($this->_responseContent->answer->{'booking-cancel'}->ok)) {
            $this->_booking->setStatus(RK_Avia_Entity_Booking::STATUS_CANCEL);
            $this->_booking->setCancelDate(RK_Core_Date::now());
        } else {
            throw new RK_Sirena_Exception('Bad confirm booking-cancel. OK node not found.');
        }


    }
}