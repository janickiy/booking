<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

class OrderViewParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if ($this->getResponse()->Body->OrderViewRS->Success) {
            $body = $this->getResponse()->Body->OrderViewRS->Response;

            // Парсинг сегментов
            foreach ($body->Order->OrderItems->OrderItem->FlightItem->OriginDestination as $OriginDestination) {
                //$OriginDestination
            }

            // Парсинг пассажиров
            $assocPassengers = array();
            foreach ($body->Passengers->Passenger as $Passenger) {
                $surname  = (string) $Passenger->Name->Surname;
                $name     = (string) $Passenger->Name->Given;
                $bornDate = new \RK_Core_Date((string) $Passenger->Age->BirthDate, '!Y-m-d');

                $passenger = $this->getBooking()->getPassengerByInfo($name, $surname, $bornDate);

                $objectKey = (string) $Passenger['ObjectKey'];
                $passenger->setRPH(str_replace('SH', '', $objectKey));

                $assocPassengers[$objectKey] = $passenger;
            }

            // Обновление пассажиров
            $this->getBooking()->setPassengers(array_values($assocPassengers));

            // Парсинг сегментов


            // Парсинг номеров билетов и привязка к сегментам
            if (isset($body->TicketDocInfos)) {
                $ticketList = [];

                foreach ($body->TicketDocInfos->TicketDocInfo as $TicketDocInfo) {
                    $TicketNumber       = (string) $TicketDocInfo->TicketDocument->TicketDocNbr;
                    $PassengerReference = (string) $TicketDocInfo->PassengerReference;

                    $ticketList[$PassengerReference] = $TicketNumber;

                    foreach ($this->getBooking()->getSegments() as $segment) {
                        // Привязка номера билета к сегментам отсутсвует, поэтому для пассажира номер билета устанавливается во все сегменты
                        if (isset($assocPassengers[$PassengerReference])) {
                            $assocPassengers[$PassengerReference]->addTicketNumber($TicketNumber, $segment->getWayNumber(), $segment->getId());
                        }
                    }
                }

                if (!empty($ticketList)) {
                    $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_TICKET);
                }

                // Проверка, что для каждого пассажира есть номер былета
                foreach ($assocPassengers as $keyPassengerRef => $assocPassenger) {
                    if (!isset($ticketList[$keyPassengerRef]) || empty($ticketList[$keyPassengerRef])) {
                        // Неполная выписка
                        $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_TICKETED_NOT_FULLY);
                    }
                }
            }

            // Парсинг данных брони
            if (isset($body->Order)) {
                // PNR
                $this->getBooking()->setLocator((string) $body->Order->BookingReferences->BookingReference->ID);

                // Таймлимит, e.g. 2018-11-09T15:16:00
                $timeLimit = (string) $body->Order->TimeLimits->PaymentTimeLimit['DateTime'];
                $this->getBooking()->setTimelimit(new \RK_Core_Date($timeLimit, \RK_Core_Date::DATE_FORMAT_SERVICES));
            }

        } elseif (isset($this->getResponse()->Body->OrderViewRS, $this->getResponse()->Body->OrderViewRS->Errors)) {
            foreach ($this->getResponse()->Body->OrderViewRS->Errors->Error as $Error) {
                $errorText = (string) $Error['ShortText'];
                $errorCode = (string) $Error['Code'];

                // TODO можно объединить в один блок
                if (preg_match('/booking\.cancelled/', $errorCode)) {
                    $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
                    $this->getBooking()->setCancelDate(\RK_Core_Date::now());

                } elseif (preg_match('/\*THIS PNR WAS ENTIRELY CANCELLED\*/', $errorText)) {
                    $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
                    $this->getBooking()->setCancelDate(\RK_Core_Date::now());

                } elseif (preg_match('/order\.not\.found/', $errorCode)) {
                    $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
                    $this->getBooking()->setCancelDate(\RK_Core_Date::now());

                }
            }

            return true;

        } else {
            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }
    }
}