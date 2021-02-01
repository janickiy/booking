<?php

use ReservationKit\src\RK;

use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price as GalileoPrice;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Passenger as GalileoPassenger;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Price as S7AgentPrice;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Passenger as S7AgentPassenger;

use ReservationKit\src\Modules\Sirena\Model\Entity\Price     as SirenaPrice;
use ReservationKit\src\Modules\Sirena\Model\Entity\Passenger as SirenaPassenger;

use ReservationKit\src\Modules\Avia\Model\AviaException;
use ReservationKit\src\Modules\Avia\Model\Exception\PassengerPriceNotSetException;

/**
 * TODO переделать по аналогии с классом Segment
 * @see Segment
 *
 * Класс описывающий авиа бронь
 */
class RK_Avia_Entity_Booking extends RK_Base_Entity_Booking
{
    /**
     * Время выписки
     *
     * @var RK_Core_Date
     */
    protected $_ticketingDate;

    /**
     * Время отмены
     *
     * @var RK_Core_Date
     */
    protected $_cancelDate;

    /**
     * Сегменты брони
     *
     * @var array
     */
    protected $_segments;

    /**
     * Пассажиры брони
     *
     * @var array
     */
    protected $_passengers;

    /**
     * Тарифы брони
     *
     * @var array
     */
    protected $_prices;

    /**
     * Реквизиты поставщика услуги
     *
     * @var
     */
    protected $_requisiteRules;

    /**
     * Реквизиты поиска услуги
     *
     * @var
     */
    protected $_requisiteId;

    /**
     * Комиссия
     *
     * @var
     */
    protected $_commission;

    /**
     * Создатель брони
     *
     * @var
     */
    protected $_creator;

    /**
     * TODO возможно это свойство лучше перенести в другой класс
     * Валидирующая компания
     *
     * @var string
     */
    protected $_validatingCompany;

    /**
     * TODO список договоров должен быть установлен в объекте creator для броинрования
     * Список 3х сторонних договоров
     *
     * @var TriPartyAgreement[]
     */
    private $_triPartyAgreements;

    /**
     * Общая стоимость
     *
     * @var \RK_Core_Money
     */
    private $_totalPrice;

    /**
     * Туркод бронирования
     *
     * @var string
     */
    protected $_tourCode;

    /**
     * Список ремарок
     *
     * @var array
     */
    protected $_remarks = array();

    public function __construct()
    {
        $this->setType(RK_Base_Entity_Booking::BOOKING_TYPE_AVIA);
    }

    /**
     * Возвращает дату выписки бронирования
     *
     * @return RK_Core_Date
     */
    public function getTicketingDate()
    {
        return $this->_ticketingDate;
    }

    /**
     * Устанавливает дату выписки бронирования
     *
     * @param RK_Core_Date $ticketingDate
     */
    public function setTicketingDate($ticketingDate)
    {
        $this->_ticketingDate = $ticketingDate;
    }

    /**
     * Возвращает дату отмены бронирования
     *
     * @return RK_Core_Date
     */
    public function getCancelDate()
    {
        return $this->_cancelDate;
    }

    /**
     * Устанавливает дату отмены бронирования
     *
     * @param RK_Core_Date $cancelDate
     */
    public function setCancelDate($cancelDate)
    {
        $this->_cancelDate = $cancelDate;
    }

    /**
     * Возвращает все сегменты бронирования
     *
     * @return Segment[]
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * Возвращает сегмент по номеру
     *
     * @param $number
     * @return null|Segment
     */
    public function getSegment($number)
    {
        return isset($this->_segments[$number]) ? $this->_segments[$number] : null;
    }

    /**
     * Возврощает списое сегментов, соответствуюших указанному номеру плеча
     *
     * @param $numWay
     * @return array
     */
    public function getSegmentsByNumWay($numWay)
    {
        $result = [];

        if ($segments = $this->getSegments()) {
            foreach ($segments as $segment) {
                if ((int) $segment->getWayNumber() === (int) $numWay) {
                    $result[] = $segment;
                }
            }
        }

        return $result;
    }

    /**
     * Возвращает первый сегмент
     *
     * @return null|Segment
     */
    public function getFirstSegment()
    {
        return $this->getSegment(0);
    }

    /**
     * Возвращает последний сегмент
     *
     * @return null|Segment|GalileoSegment
     */
    public function getLastSegment()
    {
        $countSegments = count($this->getSegments());
        return $this->getSegment($countSegments - 1);
    }

    /**
     * Устанавливает список сегментов в бронирование
     *
     * @param array $segments
     */
    public function setSegments(array $segments)
    {
        $this->_segments = $segments;
    }

    /**
     * Добавляет сегмент в конец списка сегментов в бронировании
     *
     * @param Segment $segment
     */
    public function addSegment(Segment $segment)
    {
        $this->_segments[] = $segment;
    }

    /**
     * Добавляет сегменты в конец списка сегментов в бронировании
     *
     * @param Segment[] $segments
     */
    public function addSegments(array $segments)
    {
        $this->_segments = array_merge($this->_segments, $segments);
    }

    /**
     * Возвращает список пассажиров и бронировании
     *
     * @return \RK_Avia_Entity_Passenger[]|GalileoPassenger[]|S7AgentPassenger[]
     */
    public function getPassengers()
    {
        return $this->_passengers;
    }

    /**
     * Находит и возвращает пассажира по фамилии, имени и дате рождения
     *
     * @param string $name
     * @param string $surname
     * @param RK_Core_Date $birthday
     * @return null|GalileoPassenger|S7AgentPassenger|RK_Avia_Entity_Passenger
     */
    public function getPassengerByInfo($name, $surname, RK_Core_Date $birthday)
    {
        $passengers = $this->getPassengers();

        foreach ($passengers as $passenger) {
            if (strtoupper($passenger->getFirstname()) === strtoupper($name) &&
                strtoupper($passenger->getLastname()) === strtoupper($surname) &&
                strtoupper($passenger->getBorndate()->getValue('Y-m-d')) === $birthday->getValue('Y-m-d')) {
                return $passenger;
            }
        }

        return null;
    }

    public function getPassengerByNameAndSurname($name, $surname, $type)
    {
        $passengers = $this->getPassengers();

        foreach ($passengers as $passenger) {
            if (strtoupper($passenger->getFirstname()) === strtoupper($name) &&
                strtoupper($passenger->getLastname()) === strtoupper($surname) &&
                strtoupper($passenger->getType()) === strtoupper($type)) {
                return $passenger;
            }
        }

        return null;
    }

    /**
     * Возвращает пассажира по порядковому номеру
     *
     * @param int $num Номер пассажира
     * @return GalileoPassenger|S7AgentPassenger|RK_Avia_Entity_Passenger
     * @throws AviaException
     */
    public function getPassengerByNum($num)
    {
        $passengers = $this->getPassengers();
        if (isset($passengers[$num])) {
            return $passengers[$num];
        }

        throw new AviaException('Passenger number "' . $num . '" not exist');
    }

    /**
     * Возвращает массив пассажиров определенного типа
     *
     * @param string $type Тип возвращаемых пассажиров
     * @return array
     */
    public function getPassengersByType($type)
    {
        $findPassengers = array();

        $passengers = $this->getPassengers();
        foreach ($passengers as $passenger) {
            if ($passenger->getType() === $type) {
                $findPassengers[] = $passenger;
            }
        }

        return $findPassengers;
    }

    /**
     * @param array $passengers
     */
    public function setPassengers($passengers)
    {
        $this->_passengers = $passengers;
    }

    // TODO пересмотрет филеры объектов
    public function fillPassengers($passengers)
    {
        foreach ($passengers as $passengerData) {
            $passenger = new RK_Avia_Entity_Passenger();
            $passenger->setType($passengerData['type']);
            $passenger->setFirstname($passengerData['firstName']);
            $passenger->setLastname($passengerData['lastName']);
            $passenger->setBorndate($passengerData['birthday'], 'Y-m-d');
            $passenger->setGender($passengerData['sex']);
            $passenger->setNationality($passengerData['nationality']);

            $passenger->setDocType($passengerData['docType']);
            $passenger->setDocCountry($passengerData['docCountry']);
            $passenger->setDocNumber($passengerData['docNumber']);
            $passenger->setDocExpired($passengerData['docExpired']);

            //$passenger->setEmail($passengerData['email']);
            //$passenger->setMiddlename($passengerData['middlename']);

            $this->addPassenger($passenger);
        }
    }

    /**
     * @param RK_Avia_Entity_Passenger $passenger
     */
    public function addPassenger(RK_Avia_Entity_Passenger $passenger)
    {
        $this->_passengers[] = $passenger;
    }

    /**
     * @return RK_Avia_Entity_Price[]|GalileoPrice[]|S7AgentPrice[]
     */
    public function getPrices()
    {
        return $this->_prices;
    }

    /**
     *
     * Возвращает тариф для типа пассажира
     *
     * @param string $typePassenger Тип пассажира
     * @return RK_Avia_Entity_Price|GalileoPrice|S7AgentPrice
     *
     * @param $typePassenger
     * @param bool $isException
     * @return null|GalileoPrice|S7AgentPrice|RK_Avia_Entity_Price
     */
    public function getPriceByTypePassenger($typePassenger, $isException = true)
    {
        $prices = $this->getPrices();
        if (isset($prices[$typePassenger])) {
            return $prices[$typePassenger];
        }

        // FIXME выглядит криво, надо подумать над логикой и правльностью таких условий и при возможности удалить
        // Если не нашелся тариф для INF, то пробовать взять тариф ребенка
        // Такое случается, если инфант оформлен по тарифу ребенка
        if ($typePassenger === 'INF') {
            if (isset($prices['CHD'])) {
                return $prices['CHD'];
            }
        }

        // При поиске почему-то не перехватывался кастомное исключение FIXME
        if (!$isException) {
            return null;
        }

        //throw new \Exception();

        //throw new RK_Gabriel_Exception('Price for "' . $typePassenger . '" type not set');
        throw new PassengerPriceNotSetException('Price "' . $typePassenger . '" type not set');
    }

    /**
     * Список токенов
     *
     * Одинаковый для всех прайсов
     *
     * @return mixed
     */
    public function getHostTokensForPrice()
    {
        $price = current($this->getPrices());
        return $price->getHostTokens();
    }

    /**
     * @param array $prices
     */
    public function setPrices(array $prices)
    {
        $this->_prices = $prices;
    }

    /**
     * @param $typePassenger
     * @param RK_Avia_Entity_Price $price
     */
    public function addPrice($typePassenger, RK_Avia_Entity_Price $price)
    {
        $this->_prices[$typePassenger] = $price;
    }

    /**
     * @return string
     */
    public function getValidatingCompany()
    {
        return $this->_validatingCompany;
    }

    /**
     * @param string $validatingCompany
     */
    public function setValidatingCompany($validatingCompany)
    {
        $this->_validatingCompany = $validatingCompany;
    }

    /**
     * Возвращает реквизиты поставщика
     *
     * @return \ReservationKit\src\Modules\Galileo\Model\RequisiteRules
     */
    public function getRequisiteRules()
    {
        return $this->_requisiteRules;
    }

    /**
     * Устанавливает реквизиты поставщика TODO
     *
     * @param mixed $requisiteRules
     */
    public function setRequisiteRules(\ReservationKit\src\Modules\Galileo\Model\RequisiteRules $requisiteRules)
    {
        $this->_requisiteRules = $requisiteRules;
    }

    /**
     * @return mixed
     */
    public function getRequisiteId()
    {
        return $this->_requisiteId;
    }

    /**
     * @param mixed $requisiteId
     */
    public function setRequisiteId($requisiteId)
    {
        $this->_requisiteId = $requisiteId;
    }

    /**
     * Возвращает список 3х сторонних договоров
     *
     * @return TriPartyAgreement[]
     */
    public function getTriPartyAgreements()
    {
        return $this->_triPartyAgreements;
    }

    /**
     * Возвращает 3х сторонний договор по номеру позиции
     *
     * @param $number
     * @return null|TriPartyAgreement
     */
    public function getTriPartyAgreementByNum($number)
    {
        return isset($this->_triPartyAgreements[$number]) ? $this->_triPartyAgreements[$number] : null;
    }

    /**
     * Возвращает 3х сторонний договор по коду перевозчика
     *
     * @param $code
     * @return null|TriPartyAgreement
     */
    public function getTriPartyAgreementByCarrierCode($code)
    {
        if (is_array($this->_triPartyAgreements)) {
            foreach ($this->_triPartyAgreements as $agreement) {
                if ($agreement->getCarrier() === $code) {
                    return $agreement;
                }
            }
        }

        return null;
    }

    /**
     * Устанавливает список 3х сторонних договоров
     *
     * @param TriPartyAgreement[] $triPartyAgreements
     */
    public function setTriPartyAgreements(array $triPartyAgreements = null)
    {
        $this->_triPartyAgreements = $triPartyAgreements;
    }

    /**
     * Добавляет 3х сторонний договор
     *
     * @param TriPartyAgreement $triPartyAgreement
     */
    public function addTriPartyAgreement(TriPartyAgreement $triPartyAgreement = null)
    {
        $this->_triPartyAgreements[] = $triPartyAgreement;
    }

    /**
     * @return RK_Core_Money
     * @throws RK_Core_Exception
     */
    public function getTotalPrice()
    {
        if (!isset($this->_totalPrice)) {
            $totalPrice = new \RK_Core_Money();

            foreach ($this->getPrices() as $price) {
                $totalPrice = $totalPrice->add($price->getTotalFare());
            }

            $this->_totalPrice = $totalPrice;
        }

        return $this->_totalPrice;
    }

    /**
     * Возвращает расчет строки маршрута
     *
     * @return string
     */
    public function getRouteCalc()
    {
        if (!isset($this->_routeCalc)) {
            $segmentCalcList = array();

            foreach ($this->getSegments() as $segment) {
                $segmentParams = array(
                    $segment->getDepartureCode(),
                    $segment->getArrivalCode(),
                    $segment->getOperationCarrierCode(),
                    $segment->getDepartureDate()->formatTo('dmY Hi'),
                    $segment->getArrivalDate()->formatTo('dmY Hi'),
                    $segment->getFareCode()
                );

                $segmentCalcList[] = implode(' ', $segmentParams);
            }

            $this->_routeCalc = implode(' X ', $segmentCalcList);
        }

        return $this->_routeCalc;
    }

    /**
     * Определяет равны ли брони по стоимости, маршруту и параметрам тарифа
     *
     * @param RK_Avia_Entity_Booking $booking
     * @return bool
     */
    public function isEqualOffer(\RK_Avia_Entity_Booking $booking)
    {
        if ($this->getTotalPrice()->getAmount() !== $booking->getTotalPrice()->getAmount()) {
            return false;
        }

        if ($this->getRouteCalc() !== $booking->getRouteCalc()) {
            return false;
        }

        return true;
    }

    /**
     * Определяет все ли маркетинговый компании являются российскими
     *
     * TODO пофиксить хардкод из авиакомпаний
     *
     * @return bool
     */
    public function isRussianCarrierOnly()
    {
        if (!isset($this->_isRussianCarrierOnly)) {
            $this->_isRussianCarrierOnly = true;
            foreach ($this->getSegments() as $segment) {
                if (!in_array($segment->getMarketingCarrierCode(), array('SU', 'S7', 'U6', 'UT'))) {
                    $this->_isRussianCarrierOnly = false;
                    break;
                }
            }
        }

        return $this->_isRussianCarrierOnly;
    }

    /**
     * TODO функция говорит сама за себя. Переделать и сделать ее общей для классов наследников
     *
     * Проверяет, что страна отправления соответствует коду страны $code
     * Формат $countryCode в ISO 3166-1 alpha-2 (двухбуквенный, например, RU - Россия)
     *
     * @param $countryCode
     * @return bool
     */
    public function isFlightInternal($countryCode)
    {
        // Список кодов всех аэропортов в прелете
        $airportCodeList = array();
        foreach ($this->getSegments() as $segment) {
            $airportCodeList[] = $segment->getDepartureCode();
            $airportCodeList[] = $segment->getArrivalCode();
        }

        $airportCodeList = array_unique($airportCodeList);
        /*$airportCodeList = '\'' . implode('\',\'', $airportCodeList) . '\'';

        $segmentsCountryCodeList = \ReservationKit\src\RK::getContainer()->getDbAdapterFor('catalog')
            ->query('select country_iso_code from avia_airports where iata_code IN (' . $airportCodeList . ') OR city_iata_code IN (' . $airportCodeList . ')')
            ->fetchArray();*/

        $segmentsCountryCodeList = [];
        $airports = \App\Models\References\Airport::whereIn('code',$airportCodeList)->with('country')->get();
        foreach ($airports as $airport){
            if($airport->country) $segmentsCountryCodeList[]['country_iso_code'] = $airport->country->code;
        }

        foreach ($segmentsCountryCodeList as $segmentCountryCode) {
            if ($segmentCountryCode['country_iso_code'] !== $countryCode) {
                return false;
            }
        }

        return true;
    }

    public function calculateAutoCancelTimeLimit()
    {
        $nextDay = new \DateTime('+24 hours', $this->getTimelimit()->getDateTime()->getTimezone());

        if ($this->getTimelimit()->getDateTime() > $nextDay) {
            $timeLimitCalc = $nextDay->modify('-5 minutes');
        } else {
            $timeLimitCalc = clone $this->getTimelimit()->getDateTime();
        }

        return new RK_Core_Date($timeLimitCalc);
    }

    /**
     * TODO Комиссии стоит перенсти в прайсинг
     *
     * @return mixed
     */
    public function getCommission()
    {
        return $this->_commission;
    }

    /**
     * @param mixed $commission
     */
    public function setCommission($commission)
    {
        $this->_commission = $commission;
    }

    /**
     * Возвращает туркод
     *
     * @return string
     */
    public function getTourCode()
    {
        return $this->_tourCode;
    }

    /**
     * Устанавливает туркод
     *
     * @param string $tourCode
     */
    public function setTourCode(string $tourCode)
    {
        $this->_tourCode = $tourCode;
    }

    /**
     * Определяет установлен ли туркод в бронировании
     *
     * @return bool
     */
    public function hasTourCode()
    {
        return empty($this->_tourCode) ? false : true;
    }

    /**
     * Возвращает список ремарок
     *
     * @return array
     */
    public function getRemarks()
    {
        return $this->_remarks;
    }

    /**
     * Устанавливает список ремарок
     *
     * @param array $remarks
     */
    public function setRemarks(array $remarks)
    {
        $this->_remarks = $remarks;
    }

    /**
     * Добавляет ремарку
     *
     * @param string $key Ключ ремарки
     * @param string $remark Текст ремарки
     */
    public function addRemark($key, $remark)
    {
        $this->_remarks[$key] = $remark;
    }

    /**
     * Возвращает ремарку по ключу
     *
     * @param string $key Ключ ремарки
     * @return mixed|null
     */
    public function getRemark($key)
    {
        return isset($this->_remarks[$key]) ? $this->_remarks[$key] : null;
    }

    /**
     * Возвращает базовый класс
     *
     * @return string
     */
    public function getClassBase()
    {
        $classesPriority = array(
            'Y' => 0,
            'C' => 1,
            'F' => 2,
            'W' => 3
        );

        $baseClass = '';

        if ($this->getSegments()) {
            $baseClass = 'W';

            // Поиск наихудшего класса. Этот класс будет определять класс всей брони
            foreach ($this->getSegments() as $segment) {
                $currentSegmentClass = $segment->getBaseClass();

                if ($classesPriority[$baseClass] > $classesPriority[$currentSegmentClass]) {
                    $baseClass = $currentSegmentClass;
                }
            }
        }

        return $baseClass;
    }

    /**
     * Возвращает наименование класса
     *
     * @return mixed
     */
    public function getClassType()
    {
        return Request::getTypeClassByBase($this->getClassBase());
    }

    /**
     * Возвращает общую оперирующую а/к для всех сегментов
     *
     * @return string
     */
    public function getOperatingCompanyCode()
    {
        if (!empty($this->getSegments())) {
            foreach ($this->getSegments() as $segment) {
                if (isset($operatingCarrier) && $operatingCarrier !== $segment->getOperationCarrierCode()) {
                    return false;
                }

                $operatingCarrier = $segment->getOperationCarrierCode();
            }
        }

        return '';
    }

    public function fill($data)
    {
        // Сегменты
        if (isset($data['itinerary'])) {
            $wayNumber = 0;

            foreach ($data['itinerary'] as $itinerary) {
                $segment = new \ReservationKit\src\Modules\Avia\Model\Entity\Segment();

                // Номер плеча
                if (isset($data['wayNumber'])) {
                    $segment->setWayNumber($data['wayNumber']);
                    $wayNumber = $data['wayNumber'];
                } else {
                    // Если номер плеча не указан, то устанавливается номер плеча предыдущего сегмента
                    $segment->setWayNumber($wayNumber);
                }

                // Аэропорт вылета
                $segment->setDepartureCode($itinerary['origin']);
                // Аэропорт прилета
                $segment->setArrivalCode($itinerary['destination']);

                // Датаа прилета
                if (isset($itinerary['departureTime'])) {
                    $segment->setDepartureDate(new \RK_Core_Date($itinerary['departureDate'] . ' ' . $itinerary['departureTime'], \RK_Core_Date::DATE_FORMAT_NO_SEC));
                } else {
                    $segment->setDepartureDate(new \RK_Core_Date($itinerary['departureDate'], \RK_Core_Date::DATE_FORMAT_DB_DATE));
                }

                // Дата вылета
                if (isset($itinerary['arrivalDate'], $itinerary['arrivalTime'])) {
                    $segment->setArrivalDate(new \RK_Core_Date($itinerary['arrivalDate'] . ' ' . $itinerary['arrivalTime'], \RK_Core_Date::DATE_FORMAT_NO_SEC));
                } else if (isset($itinerary['arrivalDate'])) {
                    $segment->setArrivalDate(new \RK_Core_Date($itinerary['arrivalDate'], \RK_Core_Date::DATE_FORMAT_DB_DATE));
                }

                if (isset($data['class'])) {
                    $segment->setTypeClass($data['class']);
                }

                if (isset($itinerary['class'])) {
                    $segment->setBaseClass($itinerary['class']);
                }

                if (isset($itinerary['subclass'])) {
                    $segment->setSubClass($itinerary['subclass']);
                }

                if (isset($itinerary['fareBasis'])) {
                    $segment->setFareCode($itinerary['fareBasis']);
                }

                if (isset($itinerary['operationAirline'])) {
                    $segment->setOperationCarrierCode($itinerary['operationAirline']);
                }

                if (isset($itinerary['validationAirline'])) {
                    $segment->setMarketingCarrierCode($itinerary['validationAirline']);
                }

                if (isset($itinerary['flightNumber'])) {
                    $segment->setFlightNumber($itinerary['flightNumber']);
                }

                $this->addSegment($segment);
            }
        }

        // Пассажиры
        if (isset($data['passengers'])) {
            foreach ($data['passengers'] as $passenger) {

            }
        }

        foreach (['adt', 'chd', 'inf'] as $passengerType) {
            if (isset($data[ $passengerType ]) || isset($data[ strtoupper($passengerType) ])) {
                // Тип пассажира для нижнего регистра
                if (isset($data[ $passengerType ])) {
                    $this->addPassenger(new \ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger(strtoupper($passengerType), $data[ $passengerType ]));
                }

                // Тип пассажира для верхнего регистра
                if (isset($data[ strtoupper($passengerType) ])) {
                    $this->addPassenger(new \ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger(strtoupper($passengerType), $data[ strtoupper($passengerType) ]));
                }
            }
        }

        // Реквизиты (для Galileo, Sirena)
        if (isset($data['ruid'], $data['system'])) {
            // Реквизита системы
            $requisite = \ReservationKit\src\Modules\Core\DB\Repository\RequisitesRepository::getInstance()->findById((int) $data['ruid']);
            if ($data['system'] === $requisite['system'] && !empty($requisite['requisite_rule'])) {
                // Установка правил для реквизитов
                //$requisiteRules = new \ReservationKit\src\Modules\Galileo\Model\RequisiteRules();
                //$requisiteRules->setSearchPCC($requisite['requisite_rule']->getSearchPCC());

                // Инициализация реквизитов
                \ReservationKit\src\Modules\Galileo\Model\Requisites::getInstance()->setRules($requisite['requisite_rule']);
            }
        }
    }

    /**
     * Инициализирует реквизиты бронирования
     *
     * Вызывать после сериализации объекта бронирования
     *
     * TODO вынести эту зависимость из общего класса Booking. Возможно подойдет замена этого метода хелпером. Или непосредственно в сервисы Галилео и Сирены
     *
     * @return bool
     * @throws RK_Core_Exception
     */
    public function wakeupRequisites()
    {
        if ($this->getRequisiteId()) {
            // Реквизиты бронирования
            $requisite = \ReservationKit\src\Modules\Core\DB\Repository\RequisitesRepository::getInstance()->findById((int) $this->getRequisiteId());

            // Инициализация реквизитов
            if (isset($requisite['requisite_rule'])) {
                $module = RK::getContainer()->getModule($requisite['system']);
                $module->getRequisites()
                    ->setRules($requisite['requisite_rule']);

                return true;
            }
        }

        return false;
    }
}