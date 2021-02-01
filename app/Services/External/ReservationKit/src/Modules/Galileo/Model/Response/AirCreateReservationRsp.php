<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;

class AirCreateReservationRsp extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->AirCreateReservationRsp)) {
            $body = $this->getResponse()->Body->AirCreateReservationRsp;

        } else {
            if (isset($this->getResponse()->Body->Fault)) {
                /*
                $faultcode   = (string) $body->Fault->faultcode;      //Server.Business
                $faultstring = (string) $body->Fault->faultstring;    //General air service Error.

                $AvailabilityErrorInfo = $body->Fault->detail->AvailabilityErrorInfo;
                $Code          = (string) $AvailabilityErrorInfo->Code;             // 3000
                $Service       = (string) $AvailabilityErrorInfo->Service;          // AIRSVC
                $Type          = (string) $AvailabilityErrorInfo->Type;             // Business
                $Description   = (string) $AvailabilityErrorInfo->Description;      // General air service Error.
                $TransactionId = (string) $AvailabilityErrorInfo->TransactionId;    // 70111E4B0A0764774656ADF94EE38DA7
                $TraceId       = (string) $AvailabilityErrorInfo->TraceId;          // trace
                */

                //throw new Exception((string) $body->Errors->Error[0]);

                $AvailabilityErrorInfo = $this->getResponse()->Body->Fault->detail->AvailabilityErrorInfo;

                // Добавление сообщений об ошибках
                if (isset($AvailabilityErrorInfo->AirSegmentError)) {
                    $message = (string) $AvailabilityErrorInfo->AirSegmentError->ErrorMessage;
                    $code = (string) $AvailabilityErrorInfo->Code;

                    $this->addErrorMessage($message, $code);
                }
            }

            throw new GalileoException('AirCreateReservationRsp Response not contains responseContent or booking');
        }

        if (isset($body->UniversalRecord['LocatorCode'])) {
            // Версия записи UR
            $this->getBooking()->setVersion((string) $body->UniversalRecord['Version']);

            // PNR
            $this->getBooking()->setLocator((string) $body->UniversalRecord->ProviderReservationInfo['LocatorCode']);
            $this->getBooking()->setLocatorProviderReservation((string) $body->UniversalRecord->ProviderReservationInfo['LocatorCode']);
            $this->getBooking()->setLocatorUniversalRecord((string) $body->UniversalRecord['LocatorCode']);
            $this->getBooking()->setLocatorAirReservation((string) $body->UniversalRecord->AirReservation['LocatorCode']);

            foreach ($body->UniversalRecord->AirReservation->AirPricingInfo as $AirPricingInfo) {
                // Тип пассажира берется из FareInfo для первого сегмента
                $typePassenger = (string) $AirPricingInfo->FareInfo['PassengerTypeCode'];

                // Ключ-ссылка для прайса, указывается при выписке
                $keyRef = (string) $AirPricingInfo['Key'];

                // Установка AirPricingInfoRef
                $this->getBooking()->getPriceByTypePassenger($typePassenger)->setKey($keyRef);

                // Ключ-ссылка на модификаторы прайса, используется при модификации брони
                $ticketingModifiersRef = (string) $AirPricingInfo->TicketingModifiersRef['Key'];

                // Ключ-ссылка на пассажира
                $bookingTravelerRef = (string) $AirPricingInfo->BookingTravelerRef['Key'];

                // Установка TicketingModifiersRef
                //$this->getBooking()->getPriceByTypePassenger($typePassenger)->setTicketingModifiersRef($ticketingModifiersRef);
                if ($this->getBooking()->getPassengerByKeyRef($bookingTravelerRef)) {
                    $this->getBooking()->getPassengerByKeyRef($bookingTravelerRef)->setTicketModifiersRef($ticketingModifiersRef);
                    $this->getBooking()->getPassengerByKeyRef($bookingTravelerRef)->setPriceKeyRef($keyRef);
                }
            }
			
			$segments = $this->getBooking()->getSegments();
			foreach ($body->UniversalRecord->AirReservation->AirSegment as $AirSegment) 
				for ($i=0;$i<count($segments);$i++)
					if ($segments[$i]->getFlightNumber() == $AirSegment['FlightNumber'] && $segments[$i]->getDepartureCode() == $AirSegment['Origin']
					&& $segments[$i]->getDepartureDate()->getDateTime()->format('Y-m-d') == substr($AirSegment['DepartureTime'],0,10))
					{
						$segments[$i]->setStatus((string) $AirSegment['Status']);
						break;
					}

            // Данные брони у перевозчика
            if (isset($body->UniversalRecord->AirReservation->SupplierLocator)) {
                $this->getBooking()->setLocatorSupplier((string) $body->UniversalRecord->AirReservation->SupplierLocator['SupplierLocatorCode']);
                $this->getBooking()->setCodeSupplier((string) $body->UniversalRecord->AirReservation->SupplierLocator['SupplierCode']);

                $createDateTime = new \RK_Core_Date((string) $body->UniversalRecord->AirReservation->SupplierLocator['CreateDateTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601);
                $this->getBooking()->setCreateDateSupplier($createDateTime);
            }

            $latestTicketingTime = new \RK_Core_Date((string) $body->UniversalRecord->AirReservation->AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601);
            //$latestTicketingTime->getDateTime()->sub(new \DateInterval('PT1H'));

            $this->getBooking()->setTimelimit($latestTicketingTime);
            $this->getBooking()->setBookingDate(\RK_Core_Date::now());
            $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_BOOKED);

            // Предупреждения системы
            if (isset($body->ResponseMessage)) {
                foreach ($body->ResponseMessage as $ResponseMessage) {
                    $this->addErrorMessage((string) $ResponseMessage, (string) $ResponseMessage['Code']);
                }
            }

            // Сообщения об ошибках в сегментах
            if (isset($body->AirSegmentSellFailureInfo, $body->AirSegmentSellFailureInfo->AirSegmentError)) {
                foreach ($body->AirSegmentSellFailureInfo->AirSegmentError as $AirSegmentError) {
                    $errorMessage = (string) $AirSegmentError->ErrorMessage;

                    if ($errorMessage === '*0 AVAIL/WL CLOSED*') {

                    }

                    $this->addErrorMessage($errorMessage);

                    $this->getBooking()->addErrorMessage($errorMessage);
                }
            }
        }

        return $this->getBooking();
    }
}