<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;
use ReservationKit\src\Modules\Avia\Model\Helper\SSRHelper;
use ReservationKit\src\Modules\Galileo\Model\Entity\BookingInfo as GalileoBookingInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareGuaranteeInfo as GalileoFareGuaranteeInfo;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;
use ReservationKit\src\Modules\Avia\Model\Type\SSRDataType;
use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Request;
use ReservationKit\src\Modules\Galileo\Model\Entity\Passenger as GalileoPassenger;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price as GalileoPrice;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Galileo\Model\Response\Parser\FareInfoParser;

class UniversalRecordRetrieveRsp extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->UniversalRecordRetrieveRsp)) {
            $body = $this->getResponse()->Body->UniversalRecordRetrieveRsp;
        } else {
            throw new \Exception('AirCreateReservationRsp Response not contains responseContent or booking');
        }

        if (isset($body->UniversalRecord['LocatorCode'])) {
            // Если сегменты и тарифы не прекреплены, то бронь считается отмененной
            if (!$body->UniversalRecord->AirReservation->AirPricingInfo && !$body->UniversalRecord->AirReservation->AirSegment) {
                $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);
                //$this->getBooking()->setCancelDate(\RK_Core_Date::now());

                return $this->getBooking();
            }

            if (isset($body->ResponseMessage)) {
                // Examples:
                // Unable to sync PNR JK0BR9 with host. Host error returned: 'UNABLE TO RETRIEVE - CALL HELP DESK'
                // Unable to sync PNR 29NJH2 with host. Host error returned: 'UNABLE TO RETRIEVE - CHECK RECORD LOCATOR - 29NJH2'
                $responseMessage = (string) $body->ResponseMessage;
                $pattern = "/Unable to sync PNR ([A-z0-9]){6} with host\. Host error returned: 'UNABLE TO RETRIEVE - (.*)'/";

                // Если заявка помещена в архив, то изменяем статус брони на CANCEL
                if (preg_match($pattern, $responseMessage)) {
                    $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_CANCEL);

                    return $this->getBooking();
                }
            }

            // Версия записи UR
            $this->getBooking()->setVersion((string) $body->UniversalRecord['Version']);

            // PNR
            $this->getBooking()->setLocator((string) $body->UniversalRecord->ProviderReservationInfo['LocatorCode']);
            $this->getBooking()->setLocatorProviderReservation((string) $body->UniversalRecord->ProviderReservationInfo['LocatorCode']);
            $this->getBooking()->setLocatorUniversalRecord((string) $body->UniversalRecord['LocatorCode']);
            $this->getBooking()->setLocatorAirReservation((string) $body->UniversalRecord->AirReservation['LocatorCode']);

            // Ремарки
            if (isset($body->UniversalRecord->GeneralRemark)) {
                foreach ($body->UniversalRecord->GeneralRemark as $GeneralRemark) {
                    $remarkText = (string) $GeneralRemark->RemarkData;

                    // Номер заявки
                    if (preg_match('/CLAIM ID/', $remarkText)) {
                        $this->getBooking()->addRemark('claimId', str_replace('CLAIM ID', '', $remarkText));
                    }
                }
            }

            // Данные брони у перевозчика
            if (isset($body->UniversalRecord->AirReservation->SupplierLocator)) {
                $this->getBooking()->setLocatorSupplier((string) $body->UniversalRecord->AirReservation->SupplierLocator['SupplierLocatorCode']);
                $this->getBooking()->setCodeSupplier((string) $body->UniversalRecord->AirReservation->SupplierLocator['SupplierCode']);

                $createDateTime = new \RK_Core_Date((string) $body->UniversalRecord->AirReservation->SupplierLocator['CreateDateTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601);
                $this->getBooking()->setCreateDateSupplier($createDateTime);
            }

            // Парсинг сегментов
            $i = 0;
            $AirSegmentList = array();
            foreach ($body->UniversalRecord->AirReservation->AirSegment as $AirSegment) {
                $segment = new GalileoSegment();

                $segment->setKey((string) $AirSegment['Key']);

                $segment->setWayNumber((string) $AirSegment['Group']);

                // Отправление
                $segment->setDepartureCode((string) $AirSegment['Origin']);
                $segment->setDepartureDate(new \RK_Core_Date((string) $AirSegment['DepartureTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601));
                $segment->setDepartureTerminal((string) $AirSegment->FlightDetails['OriginTerminal']);

                // Прибытие
                $segment->setArrivalCode((string) $AirSegment['Destination']);
                $segment->setArrivalDate(new \RK_Core_Date((string) $AirSegment['ArrivalTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601));
                $segment->setArrivalTerminal((string) $AirSegment->FlightDetails['DestinationTerminal']);

                // Оперирующая компания
                $operatingCarrier = isset($AirSegment->CodeshareInfo) ? (string) $AirSegment->CodeshareInfo['OperatingCarrier'] : (string) $AirSegment['Carrier'];
                $segment->setOperationCarrierCode($operatingCarrier);

                // Маркетинговая компания
                $segment->setMarketingCarrierCode((string) $AirSegment['Carrier']);

                // Номер рейса
                $segment->setFlightNumber((string) $AirSegment['FlightNumber']);

                // Время перелета
                $segment->setFlightTime((string) $AirSegment->FlightDetails['FlightTime']);
				
				// статус
                $segment->setStatus((string) $AirSegment['Status']);
				
                // Дальность перелета
                //$segment->setFlightDistance((string) $AirSegment['Distance']);

                // Номер борта
                $segment->setAircraftCode((string) $AirSegment->FlightDetails['Equipment']);

                // Тип класса сегмента
                $segment->setTypeClass((string) $AirSegment['CabinClass']);
                // Базовый класс сегмента
                $segment->setBaseClass(HelperRequest::getBaseClassByType($segment->getTypeClass()));
                // Подкласс
                $segment->setSubClass((string) $AirSegment['ClassOfService']);

                if (isset($AirSegment->Connection)) {
                    $segment->setNeedConnectionToNextSegment(true);
                }

                //$segment->setOptionalServicesIndicator((string) $AirSegment['OptionalServicesIndicator']);
                //$segment->setChangeOfPlane((string) $AirSegment['ChangeOfPlane']);
                //$segment->setTravelTime((string) $body->FlightDetailsList->FlightDetails[$i]['TravelTime']);

                $AirSegmentList[$segment->getKey()] = $segment;

                // Счетчик сегментов
                $i++;
            }

            // Парсинг прайсов
            $farePrices = array();
            $passengerCounter = array();
            $associatePassengersAndPricesKeys = array();
            $associatePassengersAndTiketingModifiersKeys = array();
            foreach ($body->UniversalRecord->AirReservation->AirPricingInfo as $AirPricingInfo) {
                $farePrice = new GalileoPrice();

                // Ключ-ссылка
                $farePrice->setKey((string) $AirPricingInfo['Key']);

                // TODO придумать как по-нормальному объединить 2 массива соответствий

                // Соответствие ключа пассажира ключу параметра AirPricingInfo
                $associatePassengersAndPricesKeys[(string) $AirPricingInfo->BookingTravelerRef['Key']] = $farePrice->getKey();

                // Соответствие ключа пассажира ключу параметра TicketingModifiersRef
                $associatePassengersAndTiketingModifiersKeys[(string) $AirPricingInfo->BookingTravelerRef['Key']] = (string) $AirPricingInfo->TicketingModifiersRef['Key'];

                // Актуальность тарифа
                $fareGuaranteeInfo = new GalileoFareGuaranteeInfo();
                $guaranteeDate = new \RK_Core_Date((string) $AirPricingInfo->PassengerType->FareGuaranteeInfo['GuaranteeDate'], \RK_Core_Date::DATE_FORMAT_DB_DATE);
                $guaranteeDate->getDateTime()->setTime(23, 59, 59);

                $fareGuaranteeInfo->setGuaranteeDate($guaranteeDate);
                $fareGuaranteeInfo->setGuaranteeType((string) $AirPricingInfo->PassengerType->FareGuaranteeInfo['GuaranteeType']);
                $farePrice->setFareGuaranteeInfo($fareGuaranteeInfo);

                // Тип пассажира
                $farePrice->setType(str_replace('CNN', 'CHD', (string) $AirPricingInfo->PassengerType['Code']));

                if (isset($passengerCounter[$farePrice->getType()])) {
                    $passengerCounter[$farePrice->getType()] = $passengerCounter[$farePrice->getType()] + 1;
                } else {
                    $passengerCounter[$farePrice->getType()] = 1;
                }

                // Количество пассажиров данного типа
                $farePrice->setQuantity($passengerCounter[$farePrice->getType()]);

                // Стоимость
                $TotalPrice = (string) $AirPricingInfo['TotalPrice'];
                $BasePrice  = (string) $AirPricingInfo['BasePrice'];
                $EquivPrice = isset($AirPricingInfo['EquivalentBasePrice']) ? (string) $AirPricingInfo['EquivalentBasePrice'] : null;

                $farePrice->setTotalFare(MoneyHelper::parseMoneyString($TotalPrice));
                $farePrice->setBaseFare(MoneyHelper::parseMoneyString($BasePrice));
                if ($EquivPrice) {
                    $farePrice->setEquivFare(MoneyHelper::parseMoneyString($EquivPrice));
                }

                // Таксы
                foreach ($AirPricingInfo->TaxInfo as $TaxInfo) {
                    $farePrice->addTax((string) $TaxInfo['Category'], MoneyHelper::parseMoneyString((string) $TaxInfo['Amount']));
                }

                $refundable = (string) $AirPricingInfo['Refundable'];
                $farePrice->setRefundable(($refundable === 'true') ? true : false);

                // Расчетная строка
                $farePrice->setFareCalc((string) $AirPricingInfo->FareCalc);

                // TODO проверить применимость метода, этот метод уже не нужен
                // Добавление ключа-ссылки на параметр TicketingModifiersRef
                $farePrice->setTicketingModifiersRef((string) $AirPricingInfo->TicketingModifiersRef['Key']);

                // TODO Валидирующая компания
                //$this->getBooking()->setValidatingCompany((string) $AirPricingInfo['PlatingCarrier']);

                // Информация о тарифе
                $FareInfoList = array();
                foreach ($AirPricingInfo->FareInfo as $FareInfo) {
                    $FareInfoList[(string) $FareInfo['Key']] = FareInfoParser::parse($FareInfo);
                }

                //
                $farePrice->setFareInfo($FareInfoList);

                // Добавление прайса в общий массив (здесь возможно переопределение значения, если ключ уже существовал)
                // TODO тут косяк, прайсы содержат уникальные данные, например, ключи-ссылки на пассажиров и tiketingModifiers! Если их перезаписывать по типам, то надо где-то сохранять эти уникальные данные (см. массив $associatePassengersAndPricesKeys)
                /* @var $farePrices GalileoPrice[] */
                $farePrices[$farePrice->getType()] = $farePrice;

                // Добавление связей сегментов и FareInfo
                foreach ($AirPricingInfo->BookingInfo as $BookingInfoItem) {
                    $bookingInfo = new GalileoBookingInfo();

                    $bookingInfo->setBookingCode((string) $BookingInfoItem['BookingCode']);
                    $bookingInfo->setCabinClass((string) $BookingInfoItem['CabinClass']);
                    $bookingInfo->setFareInfoRef((string) $BookingInfoItem['FareInfoRef']);
                    $bookingInfo->setSegmentRef((string) $BookingInfoItem['SegmentRef']);
                    $bookingInfo->setHostTokenRef((string) $BookingInfoItem['HostTokenRef']);

                    $farePrice->addBookingInfo($bookingInfo);
                }

                // Количество мест в сегменте (берется только для первого пассажира)
                foreach ($AirPricingInfo->BookingInfo as $BookingInfo) {
                    $SegmentRef = (string) $BookingInfo['SegmentRef'];
                    $FareInfoRef = (string) $BookingInfo['FareInfoRef'];

                    /* @var GalileoSegment $segment */
                    $segment = $AirSegmentList[$SegmentRef];
                    //$segment->setFareInfoRef($FareInfoRef);

                    /* @var GalileoFareInfo $fareInfo */
                    $fareInfo = $FareInfoList[$FareInfoRef];
                    $segment->setBaggage($fareInfo->getBaggageAllowance());

                    // Код тарифа
                    $segment->setFareCode($fareInfo->getFareCode());    //$segment->setBaseFare($fareInfo->getFareCode());

                    // Тип класса сегмента
                    $segment->setTypeClass((string) $BookingInfo['CabinClass']);
                    // Базовый класс сегмента
                    $segment->setBaseClass(HelperRequest::getBaseClassByType($segment->getTypeClass()));
                    // Подкласс сегмента
                    $segment->setSubClass((string) $BookingInfo['BookingCode']);
                }

                // Таймлимит
                if (isset($AirPricingInfo['TrueLastDateToTicket'])) {
                    $latestTicketingTime = new \RK_Core_Date((string) $AirPricingInfo['TrueLastDateToTicket'], \RK_Core_Date::DATE_FORMAT_ISO_8601);
                } elseif (isset($AirPricingInfo['LatestTicketingTime'])) {
                    $latestTicketingTime = new \RK_Core_Date((string) $AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601);
                } else {
                    $latestTicketingTime = new \RK_Core_Date((string) $body->UniversalRecord->ActionStatus['TicketDate'], \RK_Core_Date::DATE_FORMAT_ISO_8601);
                }

                $this->getBooking()->setTimelimit($latestTicketingTime);

                $this->getBooking()->addAirPricingInfoRef((string) $AirPricingInfo['Key']);
            }

            $createDate = new \RK_Core_Date((string) $body->UniversalRecord->AirReservation['CreateDate'], \RK_Core_Date::DATE_FORMAT_ISO_8601);

            // Коррекция временной зоны у даты создания заявки
            if ($this->getBooking()->getTimelimit() instanceof \DateTime) {
                $createDate->getDateTime()->setTimezone($this->getBooking()->getTimelimit()->getDateTime()->getTimezone());
            }

            $this->getBooking()->setBookingDate($createDate);

            // Парсинг данных пассажиров
            // +
            // Парсинг номеров билетов, если бронь выписана
            $bookingTravelers = array();
            $ticketNumbers = array();
            foreach ($body->UniversalRecord->BookingTraveler as $BookingTraveler) {
                // Данные пассажира
                $passenger = new GalileoPassenger();

                $passenger->setKey((string) $BookingTraveler['Key']);

                //
                if (isset($BookingTraveler['TravelerType'])) $passenger->setType((string) $BookingTraveler['TravelerType']);
                if (isset($BookingTraveler['DOB']))          $passenger->setBorndate((string) $BookingTraveler['DOB'], \RK_Core_Date::DATE_FORMAT_DB_DATE);
                if (isset($BookingTraveler['Gender']))       $passenger->setGender((string) $BookingTraveler['Gender']);

                // ФИО
                $passenger->setFirstname((string) $BookingTraveler->BookingTravelerName['First']);
                $passenger->setLastname((string) $BookingTraveler->BookingTravelerName['Last']);
                if (isset($BookingTraveler->BookingTravelerName['Middle'])) {
                    $passenger->setMiddlename((string) $BookingTraveler->BookingTravelerName['Middle']);
                }

                // Если у пассажира нет параметров SSR, то пропускаем его
                if (!isset($BookingTraveler->SSR)) {
                    continue;
                }

                foreach ($BookingTraveler->SSR as $SSR) {
                    if (isset($SSR['Type']) && (string) $SSR['Type'] === 'DOCS') {
                        $passenger->setDocType(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::DOC_TYPE));
                        $passenger->setDocNumber(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::DOC_NUMBER));
                        $passenger->setDocCountry(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::DOC_COUNTRY));
                        $passenger->setDocExpired(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::DOC_EXPIRED));
                        $passenger->setNationality(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::PAX_COUNTRY));

                        // Переопределение имени и фамилии на основе SSR. т.к. в BookingTravelerName могут быть приписаны префиксы фамилии
                        //$passenger->setFirstname(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::PAX_FIRSTNAME));
                        //$passenger->setLastname(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::PAX_LASTNAME));

                        // Если дата рождения не установлена, то определяем ее по данным из SSR
                        if (empty($passenger->getBorndate())) {
                            // 05JUN85
                            $DOB = SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::PAX_DOB);
                            $passenger->setBorndate(\RK_Core_Date::createFromFormat('dMy', $DOB, true));
                        }

                        // Если тип пассажира не установлен, то определяем его по дате рождения в SSR
                        if (empty($passenger->getType())) {
                            $AirSegmentListValues = array_values($AirSegmentList);
                            $lastSegment = $AirSegmentListValues[(count($AirSegmentListValues) - 1)];
                            $departureDateLastSegment = $lastSegment->getDepartureDate();
                            // Возраст пассажира на момент вылета
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

                        // Если пол не установлен, то определяем его по данным из SSR
                        if (empty($passenger->getGender())) {
                            $passenger->setGender(SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::PAX_GENDER));
                        }
                    }
                }

                // Создание уникального хеш-идентификатора для каждого пассажира
                // md5(Имя : Фамилия : Дата рождения (Y-m-d) : Номер документа)
                // TODO Странное решение, не понимаю почему сразу не устанавливаю номера билетов в данные объекта Passenger
                /*
                $hashInfo = array(
                    (string) $BookingTraveler->BookingTravelerName['First'],
                    (string) $BookingTraveler->BookingTravelerName['Last'],
                    (string) $BookingTraveler['DOB'],
                    substr((string) $this->getDocNumberForBookingTraveler($BookingTraveler), -4)
                );
                */
                //$passengerHash = /*md5(*/implode(':', $hashInfo)/*)*/;
                $passengerHash = $passenger->getHash();

                // Сбор номеров билетов для сегментов
                $numSegment = 0;
                $numWay = null;
                foreach ($BookingTraveler->SSR as $SSR) {
                    if (isset($SSR['Type']) && (string) $SSR['Type'] === 'TKNE') {
                        $ticketNumber = (string) $SSR['FreeText'];  // Пример атрибута, FreeText="0819902412572C1"
                        //$ticketNumberClean = substr($ticketNumber, 0, strrpos($ticketNumber, 'C'));

                        /** @var GalileoSegment $segment */
                        $segment = $AirSegmentList[(string) $SSR['SegmentRef']];

                        if ($numWay !== $segment->getWayNumber()) {
                            $numSegment = 0;
                        }

                        $ticketNumbers[$passengerHash][$segment->getWayNumber()][$numSegment] = $ticketNumber;

                        $numWay = $segment->getWayNumber();
                        $numSegment++;
                    }
                }

                // Добавление к пассажиру ключа-ссылки на параметр AirPricingInfo
                if (isset($associatePassengersAndPricesKeys[$passenger->getKey()])) {
                    $passenger->setPriceKeyRef($associatePassengersAndPricesKeys[$passenger->getKey()]);
                }

                // Добавление к пассажиру ключа-ссылки на параметр TicketingModifiers
                if (isset($associatePassengersAndTiketingModifiersKeys[$passenger->getKey()])) {
                    $passenger->setTicketModifiersRef($associatePassengersAndTiketingModifiersKeys[$passenger->getKey()]);
                }

                $bookingTravelers[] = $passenger;
            }

            // Парсинг OSI (other service information)
            if (isset($body->UniversalRecord->OSI)) {
                foreach ($body->UniversalRecord->OSI as $OSI) {

                    // Туркод для SU
                    if (preg_match('/^OIN(.+)/', (string) $OSI['Text'], $matches)) {
                        $this->getBooking()->setTourCode(trim($matches[1]));
                    }

                }
            }

            // Парсинг SSR
            if (isset($body->UniversalRecord->SSR)) {
                foreach ($body->UniversalRecord->SSR as $SSR) {

                    // Туркод для S7 e.g.: "/S7CIPQUW2114-VOINOV/ANDREIMR"
                    if ((string) $SSR['Type'] === 'FQTS' && preg_match('/^\/S7CIP(.+)/', (string) $SSR['FreeText'], $matches)) {
                        $FQTS = explode('-', $matches[1]);
                        if (!empty($FQTS[0])) {
                            $this->getBooking()->setTourCode($FQTS[0]);
                        }
                    }

                }
            }

            // Установка новых сегментов
            if (!empty($AirSegmentList)) {
                $this->getBooking()->setSegments(array_values($AirSegmentList));
            }

            // Установка новых прайсов
            $this->getBooking()->setPrices($farePrices);

            // Установка новых пассажиров
            if (!empty($bookingTravelers)) {
                $this->getBooking()->setPassengers($bookingTravelers);
            }

            // Обновление номеров билетов у объектов пассажиры
            $passengers = $this->getBooking()->getPassengers();
            $passengersNotIssuedTickets = array();
            if (!empty($ticketNumbers)) {
                foreach ($passengers as $passenger) {
                    if (isset($ticketNumbers[$passenger->getHash()])) {
                        $tickets = $ticketNumbers[$passenger->getHash()];

                        foreach ($tickets as $numWay => $ticketsWay) {
                            foreach ($ticketsWay as $numSegment => $ticketNumber) {
                                $passenger->addTicketNumber($ticketNumber,$numWay, $numSegment);
                            }
                        }

                    } else {
                        // Для пассажира нет номера билета (неполная выписка)
                        $passengersNotIssuedTickets[$passenger->getHash()] = $passenger;
                    }
                }
            }

            /* TODO проверить использование метода addTicketingModifiersRef , если нигде не используется то удалить. Эта ссылка находится в прайсах
            // Считываутся key TicketingModifiers. Т.к. при репрайсинге придется удалять нод по этому ключу
            if (isset($body->UniversalRecord->AirReservation->TicketingModifiers)) {
                foreach($body->UniversalRecord->AirReservation->TicketingModifiers as $TicketingModifiers) {
                    $this->getBooking()->addTicketingModifiersRef((string) $TicketingModifiers['Key']);
                }
            }
            */

            // Обновление статуса брони
            if (count($ticketNumbers) === count($passengers) && empty($passengersNotIssuedTickets)) {
                // Статус брони: выписана полностью
                $this->getBooking()->setStatus(\RK_Base_Entity_Booking::STATUS_TICKET);

            } else if (
                    count($ticketNumbers) > 0 && count($ticketNumbers) < count($passengers) ||
                    count($passengersNotIssuedTickets) > 0 && count($passengersNotIssuedTickets) < count($passengers)) {
                // Статус брони: выписана НЕ полностью
                $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_TICKETED_NOT_FULLY);

            } else {
                // Статус брони: забронирована
                $this->getBooking()->setStatus(\RK_Avia_Entity_Booking::STATUS_BOOKED);
            }
        }

        return $this->getBooking();
    }

    private function getDocNumberForBookingTraveler($BookingTraveler)
    {
        foreach ($BookingTraveler->SSR as $SSR) {
            if (isset($SSR['Type']) && (string) $SSR['Type'] === 'DOCS') {
                return SSRHelper::getFromDOCS((string) $SSR['FreeText'], SSRDataType::DOC_NUMBER);
            }
        }

        return null;
    }
}