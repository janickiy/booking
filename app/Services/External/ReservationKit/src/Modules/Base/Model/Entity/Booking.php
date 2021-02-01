<?php

/**
 * Общий обьект брони
 */
abstract class RK_Base_Entity_Booking
{
    const STATUS_NEW             = 'new';
    const STATUS_BOOKED          = 'booked';
    const STATUS_CANCEL          = 'cancel';
    const STATUS_TICKET          = 'ticket';
    const STATUS_TICKETED_NOT_FULLY = 'ticketnotfully';
    const STATUS_WAITING         = 'waiting';
    const STATUS_REJECT          = 'reject';
    const STATUS_NEED_CONFIRM    = 'needconf';
    const STATUS_PENDING_CONFIRM = 'pendconf';
    const STATUS_MANAGERHANDLING = 'managerhandling'; // Только ручная обработка менеджером

    const BOOKING_TYPE_AVIA = 'avia';

    /**
     * Номер в БД
     *
     * @var int
     */
    protected $_id;

    /**
     * Система бронирования
     *
     * @var string
     */
    protected $_system;

    /**
     * Номер в GDS
     *
     * @var string
     */
    protected $_locator;

    /**
     * Статус
     *
     * @var string
     */
    protected $_status = RK_Base_Entity_Booking::STATUS_NEW;

    /**
     * Тип брони
     *
     * @var string
     */
    protected $_type;

    /**
     * Крайний срок оплаты услуги
     *
     * @var RK_Core_Date
     */
    protected $_timelimit;

    /**
     * Дата создания брони
     *
     * @var RK_Core_Date
     */
    protected $_bookingDate;

    /**
     * Список сообщений об ошибках
     *
     * @var array
     */
    private $_errorMessages;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = (int) $id;
    }

    /**
     * @return string
     */
    public function getSystem()
    {
        return $this->_system;
    }

    /**
     * @param string $system
     */
    public function setSystem($system)
    {
        $this->_system = $system;
    }

    /**
     * @return string
     */
    public function getLocator()
    {
        return $this->_locator;
    }

    /**
     * @param string $locator
     */
    public function setLocator($locator)
    {
        $this->_locator = (string) $locator;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->_status = (string) $status;
    }

    /**
     * Возвращает тип пробнирования
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип бронирования
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = (string) $type;
    }

    /**
     * @return RK_Core_Date
     */
    public function getTimelimit()
    {
        return $this->_timelimit;
    }

    /**
     * @param RK_Core_Date $timelimit
     */
    public function setTimelimit($timelimit)
    {
        $this->_timelimit = $timelimit;
    }

    /**
     * @return RK_Core_Date
     */
    public function getBookingDate()
    {
        return $this->_bookingDate;
    }

    /**
     * @param RK_Core_Date $bookingDate
     */
    public function setBookingDate($bookingDate)
    {
        $this->_bookingDate = $bookingDate;
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->_errorMessages;
    }

    /**
     * @param array $errorMessages
     */
    public function setErrorMessages(array $errorMessages)
    {
        $this->_errorMessages = $errorMessages;
    }

    /**
     * @param string $textMessage
     */
    public function addErrorMessage($textMessage)
    {
        $this->_errorMessages[] = $textMessage;
    }
}