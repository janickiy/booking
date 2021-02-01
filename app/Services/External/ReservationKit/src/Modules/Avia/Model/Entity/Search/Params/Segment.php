<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity\Search\Params;

/**
 * Описание сегмента для поиска
 *
 * Содержит информацию о пункте вылета и прилёта, их 3х буквенные коды, дату.
 */
class Segment
{
    /**
     * Уникальный ключ-идентификатор в формате Base64UUID
     *
     * @var string
     */
    private $_key;

    /**
     * Номер плеча
     *
     * @var int
     */
    protected $_wayNumber;

    /**
     * Тип класса (Economy, Business, First)
     *
     * @var
     */
    protected $_typeClass;

    /**
     * Базовый класс (например, для Galileo: Y, C, F)
     *
     * @var
     */
    protected $_baseClass;

    /**
     * Подкласс
     *
     * @var
     */
    protected $_subClass;

    /**
     * Пункт вылета
     */
    protected $_departure;

    /**
     * Номер пункта вылета
     *
     * @var string
     */
    protected $_departureId;

    /**
     * Трехбуквенный код пункта вылета
     *
     * @var string
     */
    protected $_departureCode;

    /**
     * Пункт прилёта
     */
    protected $_arrival;

    /**
     * Номер пункта прилета
     *
     * @var string
     */
    protected $_arrivalId;

    /**
     * Трехбуквенный код пункта прилёта
     *
     * @var string
     */
    protected $_arrivalCode;

    /**
     * Время вылета
     *
     * @var \RK_Core_Date
     */
    protected $_departureDate;

    /**
     * Дата прилета
     *
     * @var \RK_Core_Date
     */
    protected $_arrivalDate;

    /**
     * Номер рейса
     *
     * @var int
     */
    protected $_flightNumber;

    /**
     * Маркетинговая компания
     *
     * @var string
     */
    protected $_marketingCarrierCode;

    /**
     * Операционная компания
     *
     * @var string
     */
    protected $_operationCarrierCode;

    /**
     * Возвращает уникальный ключ-идентификатор
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Устанавливает уникальный ключ-идентификатор
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
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
     * Возвращает класс бронирования
     *
     * @return string
     */
    public function getTypeClass()
    {
        return $this->_typeClass;
    }

    /**
     * Устанавливает класс бронирования
     *
     * @param string $typeClass
     */
    public function setTypeClass($typeClass)
    {
        $this->_typeClass = $typeClass;
    }

    /**
     * Возвращает класс бронирования
     *
     * @return string
     */
    public function getBaseClass()
    {
        return $this->_baseClass;
    }

    /**
     * Устанавливает класс бронирования
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
     * @return mixed
     */
    public function getSubClass()
    {
        return $this->_subClass;
    }

    /**
     * Устанавливает подкласс бронирования
     *
     * @param mixed $subClass
     */
    public function setSubClass($subClass)
    {
        $this->_subClass = $subClass;
    }

    /**
     * Возвращает объект пункта вылета
     *
     * @return \RK_Static_Airport
     */
    public function getDeparture()
    {
        return $this->_departure;
    }

    /**
     * Устанавливает объект пункта вылета
     *
     * @param \RK_Static_Airport $departure
     */
    public function setDeparture(\RK_Static_Airport $departure)
    {
        $this->_departure = $departure;
    }

    /**
     * Возвращает номер пункта вылета
     *
     * @return string
     */
    public function getDepartureId()
    {
        return $this->_departureId;
    }

    /**
     * Устанавливает номер пункта вылета
     *
     * @param int $departureId
     */
    public function setDepartureId($departureId)
    {
        $this->_departureId = $departureId;
    }

    /**
     * Возвращает код пункта вылета
     *
     * @return string
     */
    public function getDepartureCode()
    {
        return $this->_departureCode;
    }

    /**
     * Устанавливает код пункта вылета
     *
     * @param string $departureCode
     */
    public function setDepartureCode($departureCode)
    {
        $this->_departureCode = $departureCode;
    }

    /**
     * Возвращает пункт прилета
     *
     * @return \RK_Static_Airport
     */
    public function getArrival()
    {
        return $this->_arrival;
    }

    /**
     * Устанавливает пункт прилета
     *
     * @param \RK_Static_Airport $arrival
     */
    public function setArrival(\RK_Static_Airport $arrival)
    {
        $this->_arrival = $arrival;
    }

    /**
     * Возвращает номер пункта прилета
     *
     * @return string
     */
    public function getArrivalId()
    {
        return $this->_arrivalId;
    }

    /**
     * Устанавливает номер пункта прилета
     *
     * @param int $arrivalId
     */
    public function setArrivalId($arrivalId)
    {
        $this->_arrivalId = $arrivalId;
    }

    /**
     * Возвращает код пункта прилета
     *
     * @return string
     */
    public function getArrivalCode()
    {
        return $this->_arrivalCode;
    }

    /**
     * Устанавливает код пункта прилета
     *
     * @param string $arrivalCode
     */
    public function setArrivalCode($arrivalCode)
    {
        $this->_arrivalCode = $arrivalCode;
    }

    /**
     * Возвращает время вылета
     *
     * @return \RK_Core_Date
     */
    public function getDepartureDate()
    {
        return $this->_departureDate;
    }

    /**
     * Устанавливает время вылета
     *
     * @param \RK_Core_Date $departureDate
     */
    public function setDepartureDate(\RK_Core_Date $departureDate)
    {
        $this->_departureDate = $departureDate;
    }

    /**
     * Возвращает время прилета
     *
     * @return \RK_Core_Date
     */
    public function getArrivalDate()
    {
        return $this->_arrivalDate;
    }

    /**
     * Устанавливает время прилета
     *
     * @param \RK_Core_Date $arrivalDate
     */
    public function setArrivalDate(\RK_Core_Date $arrivalDate)
    {
        $this->_arrivalDate = $arrivalDate;
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
     * Возвращает маркетинговую компанию
     *
     * @return string
     */
    public function getMarketingCarrierCode()
    {
        return $this->_marketingCarrierCode;
    }

    /**
     * Устанавливает маркетинговую компанию
     *
     * @param string $marketingCarrierCode
     */
    public function setMarketingCarrierCode($marketingCarrierCode)
    {
        $this->_marketingCarrierCode = $marketingCarrierCode;
    }

    /**
     * Возвращает оперирующую компанию
     *
     * @return string
     */
    public function getOperationCarrierCode()
    {
        return $this->_operationCarrierCode;
    }

    /**
     * Установка оперирующей компании
     *
     * @param string $operationCarrierCode
     */
    public function setOperationCarrierCode($operationCarrierCode)
    {
        $this->_operationCarrierCode = $operationCarrierCode;
    }
}