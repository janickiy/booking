<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use App\Models\References\Airport;
use App\Models\References\City;
use Carbon\Carbon;

class BookingResultHelper
{
    /**
     * @param \RK_Avia_Entity_Booking $booking
     * @return array
     */
    public static function bookingResponseToJSON($booking)
    {
        $resultJSON = [];

        if ($booking->getLocator()) {

            // Сегменты
            $segments = $booking->getSegments();
            $segmentsArr = [];
            if (is_array($segments)) {
                foreach ($segments as $segment) {
                    // Аэропорт/город отправления
                    $origin = Airport::where('code', $segment->getDepartureCode())->with(['city', 'country'])->first();
                    if (!$origin) {
                        $origin = City::where('code', $segment->getDepartureCode())->with(['country'])->first();
                    }

                    // Аэропорт/город прибытия
                    $destination = Airport::where('code', $segment->getArrivalCode())->with(['city', 'country'])->first();
                    if (!$destination) {
                        $destination = City::where('code', $segment->getArrivalCode())->with(['country'])->first();
                    }

                    // Форматирование времени перелета
                    $flightTimeH = floor($segment->getFlightTime() / 60);
                    $flightTimeH = $flightTimeH . ' ' . trans_choice('frontend.hour', $flightTimeH);
                    $flightTimeM = $segment->getFlightTime() % 60;
                    $flightTimeM = $flightTimeM . ' ' . trans_choice('frontend.minute', $flightTimeM);

                    $segmentsArr[$segment->getWayNumber()][] = [
                        'origin' => $origin,
                        'destination' => $destination,
                        'originTerminal' => $segment->getDepartureTerminal(),
                        'destinationTerminal' => $segment->getArrivalTerminal(),
                        'departureDate' => [
                            'date' => $segment->getDepartureDate()->getValue('Y-m-d'),
                            'time' => $segment->getDepartureDate()->getValue('H:i')
                        ],
                        'arrivalDate' => [
                            'date' => $segment->getArrivalDate()->getValue('Y-m-d'),
                            'time' => $segment->getArrivalDate()->getValue('H:i')
                        ],
                        'departureDateFormatted' => [
                            'date' => Carbon::createFromFormat('Y-m-d H:i', $segment->getDepartureDate()->getValue('Y-m-d H:i'))->formatLocalized('%d %B %Y, %a'),
                            'time' => Carbon::createFromFormat('Y-m-d H:i', $segment->getDepartureDate()->getValue('Y-m-d H:i'))->formatLocalized('%H:%M')
                        ],
                        'arrivalDateFormatted' => [
                            'date' => Carbon::createFromFormat('Y-m-d H:i', $segment->getArrivalDate()->getValue('Y-m-d H:i'))->formatLocalized('%d %B %Y, %a'),
                            'time' => Carbon::createFromFormat('Y-m-d H:i', $segment->getArrivalDate()->getValue('Y-m-d H:i'))->formatLocalized('%H:%M'),
                        ],
                        'flightNumber' => $segment->getFlightNumber(),
                        'equipment' => $segment->getAircraftCode(),
                        'flightTime' => $segment->getFlightTime(),
                        'flightTimeFormatted' => $flightTimeH . ' ' . $flightTimeM,
                        'airline' => $segment->getOperationCarrierCode(),
                        'classOfService' => $segment->getSubClass(),
                        'cabinClass' => $segment->getBaseClass(),
                        'typeClass' => $segment->getTypeClass() ? $segment->getTypeClass() : FareFamilies::getInfo('baseClass', $segment->getOperationCarrierCode(), $segment->getFareCode()),
                        'fareBasis' => $segment->getFareCode(),
                        'fareName' => FareFamilies::getInfo('description', $segment->getOperationCarrierCode(), $segment->getFareCode()),
                        // TODO перенести заполнение FareFamilies на этап парсинга ответа от системы
                        'refundable'     => FareFamilies::getInfo('refundable', $segment->getOperationCarrierCode(), $segment->getFareCode()) === 'free' ? true : false,
                        'baggage'        => FareFamilies::getInfo('baggage', $segment->getOperationCarrierCode(), $segment->getFareCode()) === 'free' ? true : false,
                        'baggageMeasure' => $segment->getBaggageMeasure(),
                        'carryOn'        => FareFamilies::getInfo('carryOn', $segment->getOperationCarrierCode(), $segment->getFareCode()) === 'free' ? true : false,

                        'seats' => $segment->getAllowedSeatsBySubclass($segment->getSubClass())
                    ];
                }
            }

            // Прайсы

            // Пассажиры
            $passengers = $booking->getPassengers();
            $passengersArr = [];
            if (is_array($passengers)) {
                foreach ($passengers as $passenger) {

                    $passengersArr[] = [
                        'type' => $passenger->getType(),
                        'firstName' => $passenger->getFirstname(),
                        'lastName' => $passenger->getFirstname(),
                        'birthday' => $passenger->getBorndate('Y-m-d'),
                        'sex' => $passenger->getGender(),
                        'nationality' => $passenger->getNationality(),
                        'docType' => $passenger->getDocType(),
                        'docCountry' => $passenger->getDocCountry(),
                        'docNumber' => $passenger->getDocNumber(),
                        'docExpired' => $passenger->getDocExpired(),
                    ];

                }
            }

            $resultJSON['data'][] = [
                'bookingId' => null,
                'attributes' => [
                    'PNR' => $booking->getLocator(),
                    'segments' => $segmentsArr,
                    'passengers' => $passengersArr
                ]
            ];
        }

        return $resultJSON;
    }

}