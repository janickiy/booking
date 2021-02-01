<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Avia\Model\Type\SSRDataType;
use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Avia\Model\Helper\SSRHelper;

use ReservationKit\src\Modules\Galileo\Model\Exception\FailTicketException;

class AirTicketingRsp extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->AirTicketingRsp)) {
            $body = $this->getResponse()->Body->AirTicketingRsp;
        } else {
            throw new \Exception('AirTicketingRsp not contains responseContent or booking');
        }

        if (isset($body->Errors)) {
            //throw new RK_Gabriel_Exception((string) $body->Errors->Error[0]);
        }

        if (!isset($body->Success)) {
            //throw new RK_Gabriel_Exception('AirBook response not contain Success node');
        }

        // Сообщение об ошибках
        if (isset($body->TicketFailureInfo)) {
            foreach ($body->TicketFailureInfo as $TicketFailureInfo) {
                $this->addErrorMessage((string) $TicketFailureInfo['Message'], (string) $TicketFailureInfo['Code']);
            }
        }

        if (isset($body->ETR)) {
            $ticketNumbers = array();

            // Перебор номеров билетов из ответа системы
            foreach ($body->ETR as $ETR) {
                $BookingTraveler = $ETR->BookingTraveler;

                // Создание уникального хеш-идентификатора для каждого пассажира
                // md5(Имя : Фамилия : Дата рождения (Y-m-d) : Номер документа)
                $hashInfo = array(
                    (string) $BookingTraveler->BookingTravelerName['First'],
                    (string) $BookingTraveler->BookingTravelerName['Last'],
                    (string) $BookingTraveler['DOB'],
                    substr((string) $this->getDocsNumberForBookingTraveler($BookingTraveler), -4)
                );
                $passengerHash = /*md5(*/implode(':', $hashInfo)/*)*/;

                // Сбор номеров билетов для сегментов
                foreach ($ETR->Ticket as $Ticket) {
                    $ticketNumber = (string) $ETR->Ticket['TicketNumber'];
                    $numSegment = 0;

                    foreach ($Ticket->Coupon as $Coupon) {
                        $numWay = (string) $Coupon['SegmentGroup'];
                        $ticketNumbers[$passengerHash][$numWay][$numSegment] = $ticketNumber;
                        $numSegment++;
                    }
                }
            }

            // Установка номеров билетов в объекты пассажиров
            $passengers = $this->getBooking()->getPassengers();
            $passengersNotIssuedTickets = array();
            foreach ($passengers as $passenger) {
                if (isset($ticketNumbers[ $passenger->getHash() ])) {
                    $tickets = $ticketNumbers[$passenger->getHash()];

                    foreach ($tickets as $numWay => $ticketsWay) {
                        foreach ($ticketsWay as $numSegment => $ticketNumber) {
                            $passenger->addTicketNumber($ticketNumber,$numWay, $numSegment);
                        }
                    }

                } else {
                    // Для пассажира нет номера билета (неполная выписка)
                    $passengersNotIssuedTickets[] = $passenger;
                }
            }

            if (empty($passengersNotIssuedTickets)) {
                $this->getBooking()->setStatus(\RK_Base_Entity_Booking::STATUS_TICKET);
            }

        } else {
            throw new FailTicketException('AirTicketingRsp not contains ETR');
        }

        $listErrorMessage = $this->getListErrorMessage();
        if (!empty($listErrorMessage)) {
            throw new FailTicketException('AirTicketingRsp contains TicketFailureInfo');
        }

        return $this->getBooking();
    }

    private function getDocsNumberForBookingTraveler($BookingTraveler)
    {
        foreach ($BookingTraveler->SSR as $SSR) {
            if (isset($SSR['Type']) && (string) $SSR['Type'] === 'DOCS') {
                return SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::DOC_NUMBER);
            }
        }

        return null;
    }
}