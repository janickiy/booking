<?php

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement;

use ReservationKit\src\Modules\Galileo\Model\Helper\Request;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment as S7AgentSegment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;

/**
 * TODO в общем классе не должно быть зависимостей от дочерних классов см. функцию getSystem()
 * Обьект запроса поиска
 */
class RK_Avia_Entity_Search_Request extends RK_Base_Entity_Search_Request
{
    /**
     * TODO возвращает название системы бронирования, определяя ее по типу объека в списке сегментов
     * Надо сделать дочерний класс для каждой системы бронирования, в котором в кострукторе устанавливать
     * название системы бронирования как это сделано для объектов Booking
     */
    public function getSystem()
    {
        foreach ($this->getSegments() as $segment) {
            if ($segment instanceof S7AgentSegment) {
                $this->setSystem(SYSTEM_NAME_S7AGENT);
                return SYSTEM_NAME_S7AGENT;
            }

            if ($segment instanceof GalileoSegment) {
                $this->setSystem(SYSTEM_NAME_GALILEO_UAPI);
                return SYSTEM_NAME_GALILEO_UAPI;
            }
        }

        return null;
    }

    /**
     * Номер записи в БД
     *
     * @var
     */
    private $_id;

    /**
     * Тип поиска
     *
     * - 'OW', 'В одну сторону'
     * - 'RW', 'Туда и обратно'
     * - 'MW', 'Сложный маршрут'
     *
     * @var string
     */
    protected $_type = null;

    /**
     * Класс
     *
     * - 'ECONOMY', 'Эконом'
     * - 'BUSINESS', 'Бизнес'
     * - 'FIRST', 'Первый'
     * - 'ANY', 'Любой'
     *
     * @var string
     */
    protected $_classType = null;

    /**
     * Массив сегментов перелёта
     *
     * - 'Номер сегмента' => 'Код_сегмента'
     */
    protected $_segments = array();

    /**
     * Ассоциативный массив с информацией о кол-ве пассажиров
     *
     * Ключи массива:
     * - 'ADT' 'Врослые'
     * - 'CLD' 'Дети'
     * - 'INF' 'Младенцы'
     * - 'INS' 'Младенцы с местом'
     * Значение массива это количество людей соответствующего типа.
     *
     * @see Passenger
     * @return array
     */
    protected $_passengers = array();

    /**
     * Список авиакомпаний
     *
     * @var array
     */
    private $_carriers = array();

    /**
     * Список фвиакомпаний, исключаемых из поиска
     *
     * @var array
     */
    private $_prohibitedCarriers = array();

    /**
     * Только прямые рейсы
     *
     * @var bool
     */
    private $_direct;

    /**
     * Список 3х сторонних договоров
     * TODO этот параметр должен заменить параметры $_discount и $_tourCode
     *
     * @var TriPartyAgreement[]
     */
    private $_triPartyAgreements;

    /**
     * Скидка в процентах
     *
     * @var int
     */
    protected $_discount;

    /**
     * @var string
     */
    protected $_tourCode;

    protected $_requisiteId;

    /**
     * Возвращает номер идентификатора
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает номер идентификатора
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * @return string
     */
    public function getClassType()
    {
        if (isset($this->_classType)) {
            return $this->_classType;
        }

        return Request::getTypeClassByBase($this->getClassBase());
    }

    // TODO тупой класс, переделать в сегментах может быть не установлена буква базового класса
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

                if (isset($classesPriority[$currentSegmentClass]) && $classesPriority[$baseClass] > $classesPriority[$currentSegmentClass]) {
                    $baseClass = $currentSegmentClass;
                }
            }
        }

        return $baseClass;
    }

    /**
     * @param string $classType
     */
    public function setClassType($classType)
    {
        $this->_classType = $classType;
    }

    /**
     * @return Passenger[]
     */
    public function getPassengers()
    {
        return $this->_passengers;
    }

    public function resetPassengers()
    {
        $this->_passengers = array();
    }

    /**
     * @param mixed $passengers
     */
    public function setPassengers(array $passengers)
    {
        $this->_passengers = array();
        foreach ($passengers as $passenger) {
            // TODO перходный if
            if ($passenger instanceof Passenger) {
                $this->_passengers = $passengers;
                break;
            } else {
                $this->addPassenger($passenger['type'], 1);
            }
        }
    }

    /**
     * Добавляет нового пассажира
     *
     * Типы:
     * - 'ADT' 'Врослые'
     * - 'CLD' 'Дети'
     * - 'INF' 'Младенцы'
     * - 'INS' 'Младенцы с местом'
     *
     * @param Passenger $passenger
     */
    public function addPassenger(Passenger $passenger)
    {
        $this->_passengers[] = $passenger;
    }

    /**
     * Возвращает данные о пассажире по его типу
     *
     * @param $type
     * @return null|Passenger
     */
    public function getPassengerByType($type)
    {
        $passengers = $this->getPassengers();

        foreach ($passengers as $passenger) {
            if ($passenger->getType() === $type) {
                return $passenger;
            }
        }

        return null;
    }

    /**
     * Возвращает массив сегментов перелёта
     *
     * @return Segment[]
     */
    public function getSegments()
    {
        return $this->_segments;
    }
    /**
     * Устанавливаем массив сегментов
     *
     * @param Segment[] $segments
     */
    public function setSegments($segments)
    {
        $this->_segments = array();
        foreach($segments as $segment) {
            $this->addSegment($segment);
        }
    }

    /**
     * Добавляет сегмент перелёта
     *
     * @param Segment $segment
     */
    public function addSegment(Segment $segment)
    {
        $this->_segments[] = $segment;
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
                if ($agreement instanceof TriPartyAgreement && $agreement->getCarrier() === $code) {
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
     * Удаляет список 3х сторонних договоров
     */
    public function removeTriPartyAgreements()
    {
        $this->_triPartyAgreements = null;
    }

    /**
     * @return boolean
     */
    public function isDirect()
    {
        return ($this->_direct) ? true : false;
    }

    /**
     * @param boolean $direct
     */
    public function setDirect($direct)
    {
        $this->_direct = (bool) $direct;
    }

    /**
     * TODO
     * @return int
     */
    public function getDiscount()
    {
        return $this->_discount;
    }

    /**
     * TODO
     * @param int $discount
     */
    public function setDiscount($discount)
    {
        $this->_discount = (int) $discount;
    }

    /**
     * @return string
     */
    public function getTourCode()
    {
        return $this->_tourCode;
    }

    /**
     * @param string $tourCode
     */
    public function setTourCode($tourCode)
    {
        $this->_tourCode = $tourCode;
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
     * @param $number
     * @return null|Segment
     */
    public function getSegment($number)
    {
        $segments = $this->getSegments();
        return isset($segments[$number]) ? $segments[$number] : null;
    }

    /**
     * TODO функция говорит сама за себя. Переделать
     *
     * Проверяет, что страна отправления соответствует коду страны $code
     * Формат $countryCode в ISO 3166-1 alpha-2 (двухбуквенный, например, RU - Россия)
     *
     * @param $countryCode
     * @return bool
     */
    public function isCountryDeparture($countryCode)
    {
        $departureAirportCode = $this->getSegment(0)->getDepartureCode();
        $departureCountryCode = \ReservationKit\src\RK::getContainer()->getDbAdapterFor('catalog')
            ->query('select country_iso_code from avia_airports where iata_code = ? OR city_iata_code = ?', array($departureAirportCode, $departureAirportCode))
            ->fetchColumn('country_iso_code');

        return $countryCode === $departureCountryCode;
    }

    public function getCountryDeparture()
    {
        $departureAirportCode = $this->getSegment(0)->getDepartureCode();
        /*$departureCountryCode = \ReservationKit\src\RK::getContainer()->getDbAdapterFor('catalog')
            ->query('select country_iso_code from avia_airports where iata_code = ? OR city_iata_code = ?', array($departureAirportCode, $departureAirportCode))
            ->fetchColumn('country_iso_code');*/

        $departureCountryCode = '';
        $airport = \App\Models\References\Airport::where('code', $departureAirportCode)->with('country')->first();
        if($airport){
            if($airport->country) $departureCountryCode = $airport->country->code;
        }else{
            $city = \App\Models\References\City::where('code', $departureAirportCode)->with('country')->first();
            if($city && $city->country) $departureCountryCode = $city->country->code;
        }

        return $departureCountryCode;
    }


    /**
     * @return array
     */
    public function getCarriers()
    {
        return $this->_carriers;
    }

    /**
     * @param array $carriers
     */
    public function setCarriers($carriers)
    {
        $this->_carriers = $carriers;
    }

    /**
     * @return array
     */
    public function getProhibitedCarriers()
    {
        return $this->_prohibitedCarriers;
    }

    /**
     * @param array $prohibitedCarriers
     */
    public function setProhibitedCarriers($prohibitedCarriers)
    {
        $this->_prohibitedCarriers = $prohibitedCarriers;
    }

    /**
     * Возвращает общую оперирующую а/к для всех сегментов
     *
     * @return string
     */
    public function getValidatingCompanyCode()
    {
        if (!empty($this->getSegments())) {
            foreach ($this->getSegments() as $segment) {
                if (isset($marketingCarrier) && $marketingCarrier !== $segment->getMarketingCarrierCode()) {
                    return false;
                }

                $marketingCarrier = $segment->getMarketingCarrierCode();
            }

            return $marketingCarrier;
        }

        return '';
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

            return $operatingCarrier;
        }

        return '';
    }

    /**
     * Заполнение объекта данными из JSON
     *
     * @param $data
     */
    public function fill($data)
    {
        // Параметры запроса
        if (isset($data['class'])) {
            $this->setClassType($data['class']);
        }

        if (isset($data['type'])) {
            $this->setType($data['type']);
        }

        if (isset($data['isDirect'])) {
            $this->setDirect($data['isDirect']);
        }

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

                // Дата прилета
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
        foreach (['adt', 'chd', 'inf'] as $passengerType) {
            if (isset($data[ $passengerType ]) || isset($data[ strtoupper($passengerType) ])) {
                // Тип пассажира для нижнего регистра
                if (isset($data[ $passengerType ]) && $data[$passengerType] > 0) {
                    $this->addPassenger(new \ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger(strtoupper($passengerType), $data[ $passengerType ]));
                }

                // Тип пассажира для верхнего регистра
                if (isset($data[ strtoupper($passengerType) ]) && $data[strtoupper($passengerType)] > 0) {
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

        // Трехсторонний договор
        /*
        $triPartyAgreement1 = new \ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement('CK678', 'SBER', null, 'SU');
        $triPartyAgreement2 = new \ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement('CK678', 'SBER', null, 'UT');
        $searchRequest->addTriPartyAgreement($triPartyAgreement1);
        $searchRequest->addTriPartyAgreement($triPartyAgreement2);
        */
    }

    /**
     * Возвращает хеш запроса, указанной длины
     *
     * @param int $length
     * @return string
     */
    public function getHash($length = 32)
    {
        // Сегменты
        $segmentsString = '';
        foreach ($this->getSegments() as $segment) {
            $segmentsString = $segment->getDepartureCode() . $segment->getArrivalCode() . $segment->getDepartureDate()->getValue('Ymd');
        }

        // Пассажиры
        $passengersString = '';
        foreach ($this->getPassengers() as $passenger) {
            $passengersString = $passenger->getCount() . $passenger->getType();
        }

        $encodeString = [
            $segmentsString,
            $passengersString,
            $this->getClassType(),
        ];

        return substr(sha1(implode('', $encodeString)), 0, $length);
    }
}