<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\FareInfo;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Booking as S7AgentBooking;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment as S7AgentSegment;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Price as S7AgentPrice;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\FareInfo as S7AgentFareInfo;

use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

class OrderCreateParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->OrderViewRS, $this->getResponse()->Body->OrderViewRS->Success)) {
            $body = $this->getResponse()->Body->OrderViewRS;

        } else {
            if (isset($this->getResponse()->Body->OrderViewRS, $this->getResponse()->Body->OrderViewRS->Errors)) {
                $errorMessages = [];
                foreach ($this->getResponse()->Body->OrderViewRS->Errors as $Error) {
                    $errorMessages[] = (string) $Error;
                }

                if (!empty($errorMessages)) {
                    throw new S7AgentException(implode(";\r\n", $errorMessages));
                }
            }

            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }

        if (isset($body->Response->Order)) {
            // PNR
            $this->getBooking()->setLocator((string) $body->Response->Order->BookingReferences->BookingReference->ID);

            // Таймлимит
            $latestTicketingTime = new \RK_Core_Date((string) $body->Response->Order->TimeLimits->PaymentTimeLimit['DateTime'], \RK_Core_Date::DATE_FORMAT_SERVICES);
            //$latestTicketingTime->getDateTime()->sub(new \DateInterval('PT1H'));

            $this->getBooking()->setTimelimit($latestTicketingTime);
            $this->getBooking()->setBookingDate(\RK_Core_Date::now());
            $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_BOOKED);

            // Парсинг пассажиров
            $assocPassengers = array();
            foreach ($body->Response->Passengers->Passenger as $Passenger) {
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

            // Предупреждения системы
            /*
            if (isset($body->ResponseMessage)) {
                foreach ($body->ResponseMessage as $ResponseMessage) {
                    $this->addErrorMessage((string) $ResponseMessage, (string) $ResponseMessage['Code']);
                }
            }
            */

            // Сообщения об ошибках в сегментах
            /*
            if (isset($body->AirSegmentSellFailureInfo, $body->AirSegmentSellFailureInfo->AirSegmentError)) {
                foreach ($body->AirSegmentSellFailureInfo->AirSegmentError as $AirSegmentError) {
                    $errorMessage = (string) $AirSegmentError->ErrorMessage;

                    if ($errorMessage === '*0 AVAIL/WL CLOSED*') {

                    }

                    $this->addErrorMessage($errorMessage);

                    $this->getBooking()->addErrorMessage($errorMessage);
                }
            }
            */
        }

        return $this->getBooking();
    }
}