<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity;

/**
 * TODO:
 *  - RK_Static_Carrier
 *  - RK_Static_Airport
 *  - RK_Static_Aircraft
 */
class Segment
{
    /**
     * Id сегмента
     *
     * @var int
     */
    protected $_id;

    /**
     * Номер плеча сегмента
     *
     * @var int
     */
    protected $_wayNumber;

    /**
     * Уникальный ключ
     *
     * @var
     */
    private $_key;

    /**
     * Ключ-ссылка на тарифы
     */
    private $_fareInfoRef;

    /**
     * Код аэропорта вылета
     *
     * @var string
     */
    protected $_departureCode;

    /**
     * Терминал аэропорта вылета
     *
     * @var string
     */
    protected $_departureTerminal;

    /**
     * Код аэропорта прилета
     *
     * @var string
     */
    protected $_arrivalCode;

    /**
     * Терминал аэропорта прилета
     *
     * @var string
     */
    protected $_arrivalTerminal;

    /**
     * Код перевозчика выполняющего перелет
     *
     * @var string
     */
    protected $_operationCarrierCode;

    /**
     * Код перевозчика оформляющего перелет
     *
     * @var string
     */
    protected $_marketingCarrierCode;

    /**
     * Номер рейса
     *
     * @var int
     */
    protected $_flightNumber;

    /**
     * Код воздушного судна
     *
     * @var string
     */
    protected $_aircraftCode;

    /**
     * Дата вылета
     *
     * @var \RK_Core_Date
     */
    protected $_departureDate;

    /**
     * Интервал вермени отправления
     *
     * @var
     */
    protected $_departureTimeRange;

    /**
     * Дата прилета
     *
     * @var \RK_Core_Date
     */
    protected $_arrivalDate;

    /**
     * Базовый класс бронирования
     * Буквенный код, соответсвующий типам Economy|Business|First.
     * Для каждой GDS-системы свое соответствие букв.
     *
     * @var string
     */
    protected $_baseClass;

    /**
     * Подкласс бронирования
     *
     * @var string
     */
    protected $_subClass;

    /**
     * Тип класса бронирования
     *
     * @var string Economy|Business|First
     */
    protected $_typeClass;

    /**
     * Код тарифа
     *
     * @var string
     */
    protected $_fareCode;

    /**
     * Базовый тариф
     *
     * @var string
     */
    protected $_baseFare;

    /**
     * Длительность полета
     *
     * @var int
     */
    protected $_flightTime;

    /**
     * Дальность перелета
     *
     * @var
     */
    protected $_flightDistance;

    /**
     * @var \DateInterval
     */
    protected $_transferTime;

    /**
     * Норма багажа на рейсе
     *
     * @var int
     */
    protected $_baggage;

    /**
     * Тип измерения багажа, килограммы (K) или единицы(P)
     *
     * @var string K|P
     */
    protected $_baggageMeasure = 'K';

    /**
     * Доступно мест
     *
     * @var array
     */
    protected $_allowedSeats = array();

    /**
     * Доступные типы питания
     *
     * @var array
     */
    protected $_allowedMealTypes = array();

    /**
     * Курение разрешено
     *
     * @var bool
     */
    protected $_allowSmoking = false;

    /**
     * Параметры поставщика
     *
     * @var array
     */
    protected $_serviceParams = array();


	protected $_status = '';

    /**
     * Возвращает номер сегмента
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанвливает номер сегмента
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Возвращает уникальный ключ
     *
     * @return mixed
     */
    public function getKey()
    {
        if (empty($this->_key)) {
            $this->setKey(createBase64UUID());
        }

        return $this->_key;
    }

    /**
     * Устанавливает уникальный ключ
     *
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * Возвращает ключ ссылку на тарифы
     *
     * @return mixed
     */
    public function getFareInfoRef()
    {
        return $this->_fareInfoRef;
    }

    /**
     * Устанавливает ключ-ссылку на тарифы
     *
     * @param mixed $fareInfoRef
     */
    public function setFareInfoRef($fareInfoRef)
    {
        $this->_fareInfoRef = $fareInfoRef;
    }

    /**
     * Возвращает номер плеча
     *
     * @return int
     */
    public function getWayNumber()
    {
        return $this->_wayNumber;
    }

    /**
     * Устанавливает номер плеча
     *
     * @param $value
     */
    public function setWayNumber($value)
    {
        $this->_wayNumber = $value;
    }

    /**
     * Возвращает код аэропорта вылета
     *
     * @return string
     */
    public function getDepartureCode()
    {
        return $this->_departureCode;
    }

    /**
     * Устанвливает код аэрпорта вылета
     *
     * @param string $departureCode
     */
    public function setDepartureCode($departureCode)
    {
        $this->_departureCode = $departureCode;
    }

    /**
     * Возвращает терминал аэропорта вылета
     *
     * @return string
     */
    public function getDepartureTerminal()
    {
        return $this->_departureTerminal;
    }

    /**
     * Устанавливает терминал аэропорта вылета
     *
     * @param string $departureTerminal
     */
    public function setDepartureTerminal($departureTerminal)
    {
        $this->_departureTerminal = $departureTerminal;
    }

    /**
     * Возвращает код аэропорта прилета
     *
     * @return string
     */
    public function getArrivalCode()
    {
        return $this->_arrivalCode;
    }

    /**
     * Устанвливает код аэропорта прилета
     *
     * @param string $arrivalCode
     */
    public function setArrivalCode($arrivalCode)
    {
        $this->_arrivalCode = $arrivalCode;
    }

    /**
     * Возвращает терминал аерпорта прилета
     *
     * @return string
     */
    public function getArrivalTerminal()
    {
        return $this->_arrivalTerminal;
    }

    /**
     * Утанавливает терминал аэропрта прилета
     *
     * @param string $arrivalTerminal
     */
    public function setArrivalTerminal($arrivalTerminal)
    {
        $this->_arrivalTerminal = $arrivalTerminal;
    }

    /**
     * Возвращает код компании перевозчика
     *
     * @return string
     */
    public function getOperationCarrierCode()
    {
        return $this->_operationCarrierCode;
    }

    /**
     * Устанавливает код компании перевозчика
     *
     * @param string $operationCarrierCode
     */
    public function setOperationCarrierCode($operationCarrierCode)
    {
        $this->_operationCarrierCode = $operationCarrierCode;
    }

    /**
     * Возвращает код компании оформителя
     *
     * @return string
     */
    public function getMarketingCarrierCode()
    {
        return $this->_marketingCarrierCode;
    }

    /**
     * Устанвливает код компании оформителя
     *
     * @param string $marketingCarrierCode
     */
    public function setMarketingCarrierCode($marketingCarrierCode)
    {
        $this->_marketingCarrierCode = $marketingCarrierCode;
    }

    /**
     * Возвращает номер рейса
     *
     * @return string
     */
    public function getFlightNumber()
    {
        return $this->_flightNumber;
    }

    /**
     * Устанавливает номер рейса
     *
     * @param string $value
     */
    public function setFlightNumber($value)
    {
        $this->_flightNumber = $value;
    }

    /**
     * Возвращает код воздушного судна
     *
     * @return string
     */
    public function getAircraftCode()
    {
        return $this->_aircraftCode;
    }

    /**
     * Устанавливает код воздушного судна
     *
     * @param string $aircraftCode
     */
    public function setAircraftCode($aircraftCode)
    {
        $this->_aircraftCode = $aircraftCode;
    }

    /**
     * Возвращает дату вылета
     *
     * @return \RK_Core_Date
     */
    public function getDepartureDate()
    {
        return $this->_departureDate;
    }

    /**
     * Устанавливает дату вылета
     *
     * @param \RK_Core_Date $datetime
     */
    public function setDepartureDate(\RK_Core_Date $datetime)
    {
        $this->_departureDate = $datetime;
    }

    /**
     * @return \RK_Core_Date[]
     */
    public function getDepartureTimeRange()
    {
        return $this->_departureTimeRange;
    }

    /**
     * @param \RK_Core_Date[] $departureTimeRange
     */
    public function setDepartureTimeRange($departureTimeRange)
    {
        $this->_departureTimeRange = $departureTimeRange;
    }

    /**
     * @return null|\RK_Core_Date
     */
    public function getDepartureTimeRangeFrom()
    {
        return isset($this->_departureTimeRange['From']) ? $this->_departureTimeRange['From'] : null;
    }

    /**
     * @return null|\RK_Core_Date
     */
    public function getDepartureTimeRangeTo()
    {
        if (!is_array($this->_departureTimeRange)) {
            $this->_departureTimeRange = array();
        }

        return isset($this->_departureTimeRange['To']) ? $this->_departureTimeRange['To'] : null;
    }

    /**
     * @param \RK_Core_Date $datetime
     */
    public function setDepartureTimeRangeFrom(\RK_Core_Date $datetime)
    {
        if (!is_array($this->_departureTimeRange)) {
            $this->_departureTimeRange = array();
        }

        $this->_departureTimeRange['From'] = $datetime;
    }

    /**
     * @param \RK_Core_Date $datetime
     */
    public function setDepartureTimeRangeTo(\RK_Core_Date $datetime)
    {
        $this->_departureTimeRange['To'] = $datetime;
    }

    /**
     * Возвращает дату прилета
     *
     * @return \RK_Core_Date
     */
    public function getArrivalDate()
    {
        return $this->_arrivalDate;
    }

    /**
     * Устанавливает дату прилета
     *
     * @param \RK_Core_Date $datetime
     */
    public function setArrivalDate(\RK_Core_Date $datetime)
    {
        $this->_arrivalDate = $datetime;
    }

    /**
     * Возвращает базовый класс бронирования
     *
     * @return string
     */
    public function getBaseClass()
    {
        return $this->_baseClass;
    }

    // TODO хуита
	function get()
	{
		return $this->_needConnectionToNextSegment;
	}

    /**
     * Устанавливает базовый класс бронирования
     *
     * @param string $baseClass
     */
    public function setBaseClass($baseClass)
    {
        $this->_baseClass = $baseClass;
    }

    /**
     * Возвращает подкласс бронирования
     *
     * @return string
     */
    public function getSubClass()
    {
        return $this->_subClass;
    }

    /**
     * Устанавливает подкласс бронирования
     *
     * @param string $class
     */
    public function setSubClass($class)
    {
        $this->_subClass = $class;
    }

    /**
     * Возвращает тип класса (Economy|Business|First)
     *
     * @return string
     */
    public function getTypeClass()
    {
        return $this->_typeClass;
    }

    /**
     * Устанвливает тип класса (Economy|Business|First)
     *
     * @param string $typeClass
     */
    public function setTypeClass($typeClass)
    {
        $this->_typeClass = strtoupper($typeClass);
    }

    /**
     * Возвращает базовый тариф Fixme Проверить, где используется этот метод
     *
     * @return string
     */
    public function getBaseFare()
    {
        return $this->_baseFare;
    }

    /**
     * Устанавливает базовый тариф Fixme Проверить, где используется этот метод
     *
     * @param string $baseFare
     */
    public function setBaseFare($baseFare)
    {
        $this->_baseFare = $baseFare;
    }

    /**
     * Возвращает код тарифа
     *
     * @return string
     */
    public function getFareCode()
    {
        return $this->_fareCode;
    }

    /**
     * TODO перименовать в FareBasis ВЕЗДЕ
     *
     * Устанавливает код тарифа
     *
     * @param $fareCode
     */
    public function setFareCode($fareCode)
    {
        $this->_fareCode = $fareCode;
    }

    /**
     * Возвращает время полета
     *
     * @return int
     */
    public function getFlightTime()
    {
        return $this->_flightTime;
    }

    /**
     * Устанвливает время полета
     *
     * @param int $flightTime
     */
    public function setFlightTime($flightTime)
    {
        $this->_flightTime = $flightTime;
    }

    /**
     * Возвращает дальность перелета
     *
     * @return int
     */
    public function getFlightDistance()
    {
        return $this->_flightDistance;
    }

    /**
     * Устанавливает дальность перелета
     *
     * @param int $flightDistance
     */
    public function setFlightDistance($flightDistance)
    {
        $this->_flightDistance = $flightDistance;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    /**
     * Возвращает статус сегмента
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @return \DateInterval
     */
    public function getTransferTime()
    {
        return $this->_transferTime;
    }

    /**
     * @param \DateInterval $transferTime
     */
    public function setTransferTime($transferTime)
    {
        $this->_transferTime = $transferTime;
    }

    /**
     * TODO Добавить проверку наличия минут или часов или дней отличных от нуля
     *
     * @return bool
     */
    public function isTransfer()
    {
        return $this->getTransferTime() ? true : false;
    }

    /**
     * Возвращает допустимый багаж
     *
     * @return int
     */
    public function getBaggage()
    {
        return $this->_baggage;
    }

    /**
     * Устанавливает допустимый багаж
     *
     * @param int $value
     */
    public function setBaggage($value)
    {
        $this->_baggage = $value;
    }

    /**
     * Возвращает измерение багажа
     *
     * K - килограммы
     * P - количество штук
     *
     * @return string K|P
     */
    public function getBaggageMeasure()
    {
        return $this->_baggageMeasure;
    }

    /**
     * Устанавливает измерение багажа
     *
     * @param string $value K|P
     */
    public function setBaggageMeasure($value)
    {
        $this->_baggageMeasure = $value;
    }

    /**
     * Устанавливает доступные места
     *
     * @param array $allowedSeats
     */
    public function setAllowedSeats(array $allowedSeats)
    {
        $this->_allowedSeats = $allowedSeats;
    }

    /**
     * Возвращает количество доступных мест
     *
     * @return array
     */
    public function getAllowedSeats()
    {
        return $this->_allowedSeats;
    }

    /**
     * Возвращает количество доступных мест для указанного подкласса
     *
     * @param string $subClass Код подкласса
     * @return int|null
     */
    public function getAllowedSeatsBySubclass($subClass)
    {
        return isset($this->_allowedSeats[$subClass]) ? $this->_allowedSeats[$subClass] : null;
    }

    /**
     * Добавляет количество мест по классу
     *
     * @param $class
     * @param $countSeats
     */
    public function addAllowedSeat($class, $countSeats)
    {
        $this->_allowedSeats[$class] = $countSeats;
    }

    /**
     * Возвращает разрешено ли курение
     *
     * @return bool
     */
    public function isAllowSmoking()
    {
        return $this->_allowSmoking;
    }

    /**
     * Устанвивает разрешено ли курение
     *
     * @param bool $bool
     */
    public function setAllowSmoking($bool)
    {
        $this->_allowSmoking = (bool) $bool;
    }

    /**
     * Возвращает доступные типы питания
     *
     * @return array
     */
    public function getAllowedMealTypes()
    {
        return $this->_allowedMealTypes;
    }

    /**
     * Устанавливает доступные типы питания
     *
     * @param array $mealTypes
     */
    public function setAllowedMealTypes($mealTypes)
    {
        $this->_allowedMealTypes = $mealTypes;
    }

    /**
     * Возвращает дополнительные параметры поставщика
     *
     * @param string $type
     * @return mixed
     */
    public function getServiceParam($type)
    {
        return @$this->_serviceParams[$type];
    }

    /**
     * Устанавливает дополнительные параметры поставщика
     *
     * @param string $value метка
     * @param mixed $type значение
     */
    public function setServiceParam($value, $type = null)
    {
        $this->_serviceParams[$type] = $value;
    }
}