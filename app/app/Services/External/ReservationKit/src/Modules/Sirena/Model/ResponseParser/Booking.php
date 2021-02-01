<?php

class RK_Sirena_Response_Booking extends RK_Sirena_Response
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
     *
     * @throws RK_Sirena_Exception
     */
    public function parse()
    {
        // Наличие ответа
        if (is_null($this->_responseContent)) {
            throw new RK_Sirena_Exception('Booking response not contains responseContent');
        }

        // Сервис ответа
        if (!isset($this->_responseContent->answer)) {
            throw new RK_Sirena_Exception('Bad Booking response content');
        }


        if (isset($this->_responseContent->answer->booking)) {
            $body = $this->_responseContent->answer->booking;
        } else {
            throw new RK_Sirena_Exception('Booking response not contain booking node');
        }

        if (isset($body->error)) {
            throw new RK_Sirena_Exception('Booking error code '.$body->error["code"].' "'.$body->error.'"');
        }

        if (isset($body->pnr->regnum)) {

            $this->_booking->setLocator((string) $body->pnr->regnum);
            $this->_booking->setStatus(RK_Avia_Entity_Booking::STATUS_BOOKED);
            //$this->_booking->setTimelimit(new RK_Core_Date(str_replace('T', ' ', (string) $body->pnr->timelimit), "H:i d.m.Y")); // FIXME

            $timelimit = new RK_Core_Date(date('d.m.Y H:i', AviaDateToTime((string) $body->pnr->timelimit)), 'd.m.Y H:i');
            $this->_booking->setTimelimit($timelimit);

            $this->_booking->setBookingDate(date(RK_Core_Date::DATE_FORMAT_DB));
        }

    }
}