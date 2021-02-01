<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Passenger;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

class AirDocDisplayParser extends Response
{
    /**
     * @var Passenger
     */
    private $_passenger;

    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if ($this->getResponse()->Body->AirDocDisplayRS->Success) {
            $body = $this->getResponse()->Body->AirDocDisplayRS;

            // Сбор номеров билетов и привязка к сегментам
            foreach ($body->Response->TicketDocInfos->TicketDocInfo as $TicketDocInfo) {
                $TicketDocument = $TicketDocInfo->TicketDocument;
                $TicketNumber   = (string) $TicketDocument->TicketDocNbr;

                $name    = (string) $TicketDocInfo->Traveler->Given;
                $surname = (string) $TicketDocInfo->Traveler->Surname;
                $PTC     = (string) $TicketDocInfo->Traveler->PTC;

                $passenger = $this->getBooking()->getPassengerByNameAndSurname($name, $surname, $PTC);

                if ($passenger) {
                    foreach ($TicketDocument->CouponInfo as $CouponInfo) {
                        $DepartureCode        = (string) $CouponInfo->SoldAirlineInfo->Departure->AirportCode;
                        $ArrivalCode          = (string) $CouponInfo->SoldAirlineInfo->Arrival->AirportCode;
                        $MarketingCarrierCode = (string) $CouponInfo->SoldAirlineInfo->MarketingCarrier->AirlineID;
                        $FlightNumber         = (string) $CouponInfo->SoldAirlineInfo->MarketingCarrier->FlightNumber;

                        $ticketNumberBySegment[$DepartureCode . '/' . $ArrivalCode . '/' . $MarketingCarrierCode . '/' . $FlightNumber] = $TicketNumber;
                    }

                    foreach ($this->getBooking()->getSegments() as $segment) {
                        $DepartureCode        = $segment->getDepartureCode();;
                        $ArrivalCode          = $segment->getArrivalCode();
                        $MarketingCarrierCode = $segment->getMarketingCarrierCode();
                        $FlightNumber         = $segment->getFlightNumber();

                        if (isset($ticketNumberBySegment[$DepartureCode . '/' . $ArrivalCode . '/' . $MarketingCarrierCode . '/' . $FlightNumber])) {
                            $passenger->addTicketNumber($TicketNumber, $segment->getWayNumber(), $segment->getId());
                        }
                    }

                    if (empty($passenger->getTicketNumbers())) {
                        return false;
                    }

                } else {
                    // Выписан пассажир которого нет в объекте Booking
                }
            }

            return true;

        } elseif (isset($this->getResponse()->Body->AirDocDisplayRS, $this->getResponse()->Body->AirDocDisplayRS->Errors)) {
            // Здесь обработка ошибок

        } else {
            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }
    }

    /**
     * @return Passenger
     */
    public function getPassenger(): Passenger
    {
        return $this->_passenger;
    }

    /**
     * @param Passenger $passenger
     */
    public function setPassenger(Passenger $passenger)
    {
        $this->_passenger = $passenger;
    }
}