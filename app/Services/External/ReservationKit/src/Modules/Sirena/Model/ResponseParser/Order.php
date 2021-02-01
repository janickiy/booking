<?php

class RK_Sirena_Response_Order extends RK_Sirena_Response
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
            throw new RK_Sirena_Exception('Bad order response content');
        }


        if (isset($this->_responseContent->answer->order)) {
            $body = $this->_responseContent->answer->order;

            if (!isset($body->pnr)) {
                throw new RK_Sirena_Exception('Order response not contain PNR node');
            }

        } else {
            throw new RK_Sirena_Exception('Order response not contain order node');
        }

        //throw new RK_Sirena_Exception('status:'.(string)$body->pnr->common_status);

        /**
         * Возможные значения параметра <common_status>:
            not sold 	        Заказ не выкуплен, таймлимит еще не истек
            cancelled 	        Заказ не выкуплен, таймлимит истек
            mso 	            Напечатаны квитанции MCO
            ticket 	            Напечатаны билеты
            ticket(s) returned 	Билеты сданы
            mco returned 	    Квитанции сданы
            being_paid_for 	    Заказ ожидает подтверждения оплаты
         */


        //throw new Exception((string)$body->pnr->common_status);

        if (!sizeof((array)$body->pnr->segments) || (string)$body->pnr->common_status == "cancelled") {
            $this->_booking->setStatus(RK_Avia_Entity_Booking::STATUS_CANCEL);
        } elseif ((string)$body->pnr->common_status == "ticket") {
            $this->_booking->setStatus(RK_Avia_Entity_Booking::STATUS_TICKET);

            // Дальше полный пиздец!
            // Всё смешалось в доме Облонских...

            // Сохраняем пассажиров для дальнейшей связки их с ID
            $orderPassengers = array();
            foreach ($body->pnr->passengers->passenger as $passenger) {
                $doc_type = (string)$passenger->doccode; // Тип документа
                $doc_number = (string)$passenger->doc; // Номер документа

                $orderPassengers[$doc_type.$doc_number] = (string)$passenger["id"];
            }

            $tickets = array();
            foreach ($body->tickinfo as $ticket) {

                $pass_id = (string)$ticket["pass_id"];

                $tmp = array();
                $tmp["tick_ser"] = (string)$ticket["tick_ser"];
                $tmp["is_etick"] = (string)$ticket["is_etick"];
                $tmp["accode"] = (string)$ticket["accode"];
                $tmp["tkt_ppr"] = (string)$ticket["tkt_ppr"];
                $tmp["print_time"] = (string)$ticket["print_time"];
                $tmp["segment_id"] = (string)$ticket["seg_id"];
                $tmp["ticknum"] = (string)$ticket["ticknum"];

                $tickets[$pass_id][] = $tmp;
            }

            //throw new Exception(print_r($tickets, true));


            /* @var RK_Avia_Entity_Passenger $passenger */
            foreach ($this->_booking->getPassengers() as $passenger) {
                $passenger->resetTicketNumbers();
                $doc_type = $passenger->getDocType(); // Тип документа
                $doc_number = $passenger->getDocNumber(); // Номер документа

                // ID пассажира
                $pass_id = $orderPassengers[$doc_type.$doc_number];

                // Добавляем билеты к пассажиру
                foreach ($tickets[$pass_id] as $ticket) {
                    $segment = $this->_booking->getSegment($ticket["segment_id"] - 12);
                    $passenger->addTicketNumber($ticket["ticknum"], $segment->getId(), $segment->getJourneyNumber());
                }
            }

        } else {
            $this->_booking->setStatus($status = RK_Avia_Entity_Booking::STATUS_BOOKED);
        }

        if ($this->_booking->getStatus() != RK_Avia_Entity_Booking::STATUS_CANCEL) {

        }

    }
}