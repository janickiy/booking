<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement;

class SearchRequestOrBookingChecker
{
    /**
     * Проверяет установлены ли в объекте только взрослые пассажиры
     *
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     * @return bool
     */
    public static function isAdultOnly($searchRequestOrBooking)
    {
        $passengers = $searchRequestOrBooking->getPassengers();

        foreach ($passengers as $passenger) {
            if ($passenger->getType() !== 'ADT') {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет, все ли сегменты принадлежат одному перевозчику
     *
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     * @param $carrierCode
     * @return bool
     */
    public static function isCarrierOnly($searchRequestOrBooking, $carrierCode)
    {
        $segments = $searchRequestOrBooking->getSegments();
        foreach ($segments as $segment) {
            if ($segment->getMarketingCarrierCode() !== $carrierCode) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \RK_Avia_Entity_Booking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     * @param TriPartyAgreement[] $triPartyAgreements
     * @return null|TriPartyAgreement
     */
    public static function getValidTriPartyAgreement($searchRequestOrBooking, $triPartyAgreements)
    {
        if (is_array($triPartyAgreements)) {
            foreach ($triPartyAgreements as $triPartyAgreement) {
                if (self::isCarrierOnly($searchRequestOrBooking, $triPartyAgreement->getCarrier())) {
                    return $triPartyAgreement;
                }
            }
        }

        return null;
    }
}