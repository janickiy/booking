<?php

/*
 * class RK_Sirena_Response_TicketingAction
 * Ответ на подготовительный запрос ввода информации об оплате заказа (action)
 */

class RK_Sirena_Response_TicketingConfirm extends RK_Sirena_Response
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
            throw new RK_Sirena_Exception('Bad '.__CLASS__.' response content');
        }


        if (isset($this->_responseContent->answer->{'payment-ext-auth'})) {
            $body = $this->_responseContent->answer->{'payment-ext-auth'};

        } else {
            throw new RK_Sirena_Exception(__CLASS__.' response not contain payment-ext-auth node');
        }

        if (isset($body->error)) {
            throw new RK_Sirena_Exception(__CLASS__.' error code '.$body->error["code"].' "'.$body->error.'"');
        }


        if (!isset($body->ok)) {
            throw new RK_Sirena_Exception('Bad confirm '.__CLASS__.' OK node not found.');
        }

    }
}