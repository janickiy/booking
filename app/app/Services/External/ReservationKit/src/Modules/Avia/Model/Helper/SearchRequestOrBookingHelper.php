<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;

class SearchRequestOrBookingHelper
{
    /**
     * Возвращает список типов пассажиров и их количество в зависимости от переданного объекта
     *
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     * @return Passenger[]
     */
    public static function getAnonymousPassengers($searchRequestOrBooking)
    {
        $passengers = array();

        if ($searchRequestOrBooking instanceof \RK_Avia_Entity_Search_Request) {
            $passengers = $searchRequestOrBooking->getPassengers();

        } else if ($searchRequestOrBooking instanceof \RK_Avia_Entity_Booking) {
            $quantityPassengersType = array();
            foreach ($searchRequestOrBooking->getPassengers() as $passenger) {
                $quantityPassengersType[$passenger->getType()] = isset($quantityPassengersType[$passenger->getType()]) ? $quantityPassengersType[$passenger->getType()] + 1 : 1;
            }

            foreach ($quantityPassengersType as $type => $quantity) {
                $passengers[] = new Passenger($type, $quantity);
            }
        }

        return $passengers;
    }
}