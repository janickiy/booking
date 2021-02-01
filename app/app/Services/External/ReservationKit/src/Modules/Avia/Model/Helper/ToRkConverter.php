<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Entity\Passenger as GalileoPassenger;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price as GalileoPrice;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;

use ReservationKit\src\Modules\S7AgentApi\Model\Entity\Passenger as S7AgentPassenger;
use ReservationKit\src\Modules\S7AgentApi\Model\Entity\Segment as S7AgentSegment;

/**
 * Класс конвертор из формата сайта (массив) в объекты ReservationKit
 *
 * @package ReservationKit\src\Modules\Avia\Model\Helper
 */
class ToRkConverter
{
    /**
     * Преобразование данных пассажиров в бронируемом перелете в формат модуля ReservationKit
     *
     * @param array $passengers
     * @param \RK_Avia_Entity_Passenger|GalileoPassenger|null $passengerObject
     * @return array
     */
    public static function getRkPassengers(array $passengers, \RK_Avia_Entity_Passenger $passengerObject = null, $email = '')
    {
        $passengersRK = array();
        foreach ($passengers as $passenger) {
            if ($passengerObject instanceof \RK_Avia_Entity_Passenger) {
                // Используется у объектов Galileo для установки параметра key
                /* @var GalileoPassenger */
                $passengerRK = clone $passengerObject;
            } else {
                $passengerRK = new \RK_Avia_Entity_Passenger();
            }

            if (isset($passenger['key'])) $passengerRK->setKey($passenger['key']);

            $passengerRK->setId($passenger['passenger_id']);
            $passengerRK->setType($passenger['type']);

            $passengerRK->setFirstname(\RK_Core_Helper_String::translit(strtoupper(preg_replace('/[^A-zА-я]/', '', $passenger['firstname']))));
            $passengerRK->setLastname(\RK_Core_Helper_String::translit(strtoupper(preg_replace('/[^A-zА-я]/', '', $passenger['lastname']))));
            $passengerRK->setMiddlename(\RK_Core_Helper_String::translit(strtoupper(preg_replace('/[^A-zА-я]/', '', $passenger['middlename']))));

            $passengerRK->setGender($passenger['sex']);
            $passengerRK->setBorndate($passenger['birthday'], '!Y-m-d');
            $passengerRK->setNationality($passenger['nation']);

            $passengerRK->setDocType($passenger['document_type']);
            $passengerRK->setDocNumber($passenger['document_number']);
            $passengerRK->setDocCountry($passenger['document_country']);

            $docExpired = $passenger['document_date'] ? $passenger['document_date'] : '2025-11-07';
            $passengerRK->setDocExpired($docExpired, '!Y-m-d');

            $passengerRK->setPhone($passenger['phone_sms']);

			if ($passenger['email']) $passengerRK->setEmail($passenger['email']);
			elseif ($email) $passengerRK->setEmail($email);

			for ($m=0;$m<count($passenger['milecards']);$m++)
			{
			    if (isset($passenger['milecards'][$m]['airline']))  {
                    if ($passenger['milecards'][$m]['airline'] == 'S7') $passengerRK->setFQTV($passenger['milecards'][$m]['number']);
                    $passengerRK->addLoyaltyCard($passenger['milecards'][$m]['airline'], $passenger['milecards'][$m]['number']);
                }
			}

            $passengersRK[] = $passengerRK;
        }
        return $passengersRK;
    }

    /**
     * Преобразование данных сегментов в бронируемом перелете в формат модуля ReservationKit
     *
     * @param array $segments
     * @param array $segmentsInfo
     * @param Segment|null $segmentObject
     * @return array
     */
    public static function getRkSegments(array $segments, array $segmentsInfo = array(), Segment $segmentObject = null)
    {
		global $CONFIG;
		
        $segmentsRK = array();
        foreach ($segments as $segmentNum => $segment) {
            if ($segmentObject) {
                /* @var GalileoSegment $segmentRK */
                $segmentRK = clone $segmentObject;
            } else {
                $segmentRK = new Segment();
            }

            $segmentRK->setId($segmentNum);

            if (isset($segment['key']))            $segmentRK->setKey($segment['key']);
            if (isset($segment['fareInfoRef']))    $segmentRK->setFareInfoRef($segment['fareInfoRef']);
            if (isset($segment['section_number'])) $segmentRK->setWayNumber($segment['section_number']);

            if (isset($segment['airport_out']))  $segmentRK->setDepartureCode($segment['airport_out']);
            if (isset($segment['airport_in']))   $segmentRK->setArrivalCode($segment['airport_in']);

            if (isset($segment['terminal_out'])) $segmentRK->setDepartureTerminal($segment['terminal_out']);
            if (isset($segment['terminal_in']))  $segmentRK->setArrivalTerminal($segment['terminal_in']);

            // После смены формата дата/время у сегментов на новый (2017-07-03 21:25:00)
            // остались брони со старым форматом (20170703212500), этот формат тоже надо обрабатывать
            $departureDate = new \RK_Core_Date($segment['datetime_out'], 'Y-m-d H:i:s');
            $arrivalDate = new \RK_Core_Date($segment['datetime_in'], 'Y-m-d H:i:s');

            if (!$departureDate->getDateTime() instanceof \DateTime) {
                $departureDate = new \RK_Core_Date($segment['datetime_out'], 'YmdHis');
            }

            if (!$arrivalDate->getDateTime() instanceof \DateTime) {
                $arrivalDate = new \RK_Core_Date($segment['datetime_in'], 'YmdHis');
            }

            if (isset($segment['datetime_out'])) $segmentRK->setDepartureDate($departureDate);
            if (isset($segment['datetime_in']))  $segmentRK->setArrivalDate($arrivalDate);

            // Переопределение даты вылета с учетом таймзоны аэропорта вылета
            if ($segment['timezone_out']) {
                $dateWithTimeZOne = new \DateTime($segmentRK->getDepartureDate(), new \DateTimeZone($segment['timezone_out']));

                $segmentRK->setDepartureDate(new \RK_Core_Date($dateWithTimeZOne));
            }

            // Переопределение даты прилета с учетом таймзоны аэропорта прилета
            if ($segment['timezone_in']) {
                $dateWithTimeZOne = new \DateTime($segmentRK->getArrivalDate(), new \DateTimeZone($segment['timezone_in']));

                $segmentRK->setArrivalDate(new \RK_Core_Date($dateWithTimeZOne));
            }

            if (isset($segment['number']))       $segmentRK->setFlightNumber($segment['number']);
            if (isset($segment['aircraft']))     $segmentRK->setAircraftCode($segment['aircraft']);
            if (isset($segment['flight_time']))  $segmentRK->setFlightTime($segment['flight_time']);

            if (isset($segment['airline_operating'])) $segmentRK->setOperationCarrierCode($segment['airline_operating']);
            if (isset($segment['airline']))           $segmentRK->setMarketingCarrierCode($segment['airline']);

            // Connection
            if (isset($segment['connection']) && $segmentRK instanceof GalileoSegment) {
                $segmentRK->setNeedConnectionToNextSegment($segment['connection']);
            }

            if (isset($segmentsInfo[$segmentNum])) {
                $segmentInfo = $segmentsInfo[$segmentNum];

                if (isset($segmentInfo['class']))    $segmentRK->setBaseClass($segmentInfo['class']);
                if (isset($segmentInfo['BIC']))      $segmentRK->setSubClass($segmentInfo['BIC']);
                if (isset($segmentInfo['meals']))    $segmentRK->setAllowedMealTypes($segmentInfo['meals']);
                if (isset($segmentInfo['services'])) $segmentRK->setServiceParam($segmentInfo['services']);
                if (isset($segmentInfo['FIC']))      $segmentRK->setBaseFare($segmentInfo['FIC']);
                if (isset($segmentInfo['baggage']))  $segmentRK->setBaggage($segmentInfo['baggage']);
                if (isset($segmentInfo['FIC']))      $segmentRK->setFareCode($segmentInfo['FIC']);
            }

            $segmentsRK[] = $segmentRK;
        }

        return $segmentsRK;
    }

    public static function getRkDocuments()
    {

    }

    /**
     * Преобразование данных о бронируемом перелете в формат модуля ReservationKit
     *
     * @param array $params
     * @param \RK_Avia_Entity_Booking|GalileoBooking|null $bookingObject
     * @return \RK_Avia_Entity_Booking|GalileoBooking
     */
    public static function getRkBooking(array $params, \RK_Avia_Entity_Booking $bookingObject = null)
    {
        $passengerObj = null;
        $segmentObj   = null;

        if ($bookingObject instanceof \RK_Avia_Entity_Booking) {
            $bookingRK = $bookingObject;
            if ($bookingRK->getSystem() === 'galileo') {
                $passengerObj = new GalileoPassenger();
                $segmentObj   = new GalileoSegment();
            }

            if ($bookingRK->getSystem() === 'S7Agent') {
                $passengerObj = new S7AgentPassenger();
                $segmentObj   = new S7AgentSegment();
            }

        } else {
            $bookingRK = new \RK_Avia_Entity_Booking();
        }

        if (isset($params['passengers'])) {
            $passengers = self::getRkPassengers($params['passengers'], $passengerObj, $params['email']);
            $bookingRK->setPassengers($passengers);
        }

        if (isset($params['passengers'], $params['segments'])) {
            $segments = self::getRkSegments($params['segments'], $params['passengers'][0]['segments_info'], $segmentObj);
            $bookingRK->setSegments($segments);

            // FIXME установка валидирующей компании. Как-то криво. Надо заменить маркетинговую компанию на валидирующую. Удалить метод
            $bookingRK->setValidatingCompany($bookingRK->getSegment(0)->getMarketingCarrierCode());

            // Установка возраста пассажиров. Возраст относительно даты вылета в последнем сегменте
            self::setPassengersAge($bookingRK);

            // Проверка установлен ли тип пассажира (на момент вылета в первом сегменте)
            //self::checkTypePassenger($bookingRK);
        }

        // Идентификаторы
        if (isset($params['id']))  $bookingRK->setId($params['id']);
        if (isset($params['PNR'])) $bookingRK->setLocator($params['PNR']);

        // Параметры специфичные для GalileoBooking
        /* @see GalileoBooking */
        if ($bookingRK instanceof GalileoBooking) {
            if (isset($params['PNR']))           $bookingRK->setLocatorProviderReservation($params['PNR']);
            if (isset($params['PNR_universal'])) $bookingRK->setLocatorUniversalRecord($params['PNR_universal']);
            if (isset($params['PNR_aircreate'])) $bookingRK->setLocatorAirReservation($params['PNR_aircreate']);
            if (isset($params['version_UR']))    $bookingRK->setVersion($params['version_UR']);
        }

        // FIXME в названии указывать только gabriel, galileo, sabre, sirena. Не должно быть префиксов и постфиксов
        $bookingRK->setSystem($params['system']);

        // TODO фикс, который используется автоотмены брони
        //$bookingRK->setIsBrand($params['isBrand']);

        if (isset($params['RkPrice'])) {
            $prices = null;
            $uncompress = @gzuncompress(base64_decode($params['RkPrice']));
            if ($uncompress !== false) {
                $prices = unserialize($uncompress);
            }
            
            if (!is_array($prices)) {
                // Совместимость со старыми бронями
                $prices = unserialize(urldecode(base64_decode($params['RkPrice'])));
            }
            $bookingRK->setPrices($prices);

            /** @var \RK_Avia_Entity_Price|GalileoPrice $price */
            $prices = $bookingRK->getPrices();
            $prices = array_values($prices);
            $price  = $prices[0];

            if (!empty($data['restrictive_time'])) {
                $bookingRK->setTimelimit(new \RK_Core_Date((int) $data['restrictive_time']));
            } else if ($price instanceof GalileoPrice && $price->getTicketTimelimit()) {
                $bookingRK->setTimelimit($price->getTicketTimelimit());
            } else if ($bookingRK instanceof GalileoBooking) {
                $bookingRK->setTimelimit($bookingRK->calculateTiсketTimelimit());
            }

            // Обновление ключей в сегментах
            if ($price instanceof GalileoPrice && $price->getBookingInfoList()) {
                foreach ($price->getBookingInfoList() as $num => $bookingInfo) {
                    $segment = $bookingRK->getSegment($num);

                    if (isset($segment)) {
                        $segment->setKey($bookingInfo->getSegmentRef());
                        $segment->setFareInfoRef($bookingInfo->getFareInfoRef());
                    }
                }
            }
        }

        // Статус бронирования
        switch ($params['status']) {
            case 'success':
                $bookingRK->setStatus(\RK_Avia_Entity_Booking::STATUS_BOOKED);
                break;
            case 'ticketed':
                $bookingRK->setStatus(\RK_Avia_Entity_Booking::STATUS_TICKET);
                break;
            case 'annuled':
                $bookingRK->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
                break;
        }

        return $bookingRK;
    }

    /**
     * Устанавливает возраст пассажиров. TODO придумать нормальную логику
     *
     * @param \RK_Avia_Entity_Booking $booking
     */
    public static function setPassengersAge(\RK_Avia_Entity_Booking $booking)
    {
        $passengers = $booking->getPassengers();
        $departureDateLastSegment = $booking->getLastSegment()->getDepartureDate();

        foreach ($passengers as $passenger) {
            $ageAboutDate = $passenger->getAgeAboutDate($departureDateLastSegment);
            $passenger->setAge($ageAboutDate);
        }
    }

    public static function checkTypePassenger(\RK_Avia_Entity_Booking $booking)
    {
        $passengers = $booking->getPassengers();
        $departureDateLastSegment = $booking->getLastSegment()->getDepartureDate();

        foreach ($passengers as $passenger) {
            if (!empty($passenger->getType())) {
                continue;
            }

            $ageAboutDate = $passenger->getAgeAboutDate($departureDateLastSegment);

            if ($ageAboutDate >= 12) {
                $passenger->setType('ADT');
            }

            if ($ageAboutDate < 12) {
                $passenger->setType('CHD');
            }

            if ($ageAboutDate < 2) {
                $passenger->setType('INF');
            }
        }
    }

    /**
     * @param $agreements
     * @param string $percentType economy|business
     * @return array|null
     */
    public static function getRkTriPartyAgreements($agreements, $percentType = 'economy')
    {
        if (is_array($agreements)) {
            $rkAgreements = array();

            $percentType = (strtolower((string) $percentType) === 'business') ? '_business': '';

            foreach ($agreements as $agreement) {
                /*
                $expired = \DateTime::createFromFormat('d.m.Y', $agreement['expired']);
                $now = new \Datetime();

                if ($expired instanceof \DateTime && $expired < $now) {
                    continue;
                }
                */

                $triPartyAgreement = new TriPartyAgreement();
                $triPartyAgreement->setTourCode($agreement['tour_code']);
                $triPartyAgreement->setAccountCode($agreement['tour_code2']);
                $triPartyAgreement->setDiscount($agreement['percent'.$percentType]);

                // Если есть ключ скидки для бизнес класса, но значение этого ключа отсутствует, то использовать скидку для эконома
                if (!empty($percentType) && empty($agreement['percent' . $percentType])) {
                    $triPartyAgreement->setDiscount($agreement['percent']);
                }

                $triPartyAgreement->setCarrier($agreement['airline']);

                $rkAgreements[] = $triPartyAgreement;;
            }
			
            return $rkAgreements;
        }

        return null;
    }
}