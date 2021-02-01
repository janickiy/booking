<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class Passenger extends \RK_Avia_Entity_Passenger
{
    /**
     * Уникальный ключ-идентификатор в формате Base64UUID
     *
     * @var string
     */
    private $_key;

    private $_priceKeyRef;

    private $_ticketModifiersRef;

    public function __construct()
    {
        $this->setKey(createBase64UUID());
    }

    public function __clone()
    {
        $this->setKey(createBase64UUID());
    }

    /**
     * Возвращает ключ-идентификатор
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Устанавливает ключ-идентификатор
     *
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->_key = $key;
    }

    /**
     * Возвращает ключ-ссылку прайса
     *
     * @return mixed
     */
    public function getPriceKeyRef()
    {
        return $this->_priceKeyRef;
    }

    /**
     * Устанавливает ключ-ссылку прайса
     *
     * @param mixed $priceKeyRef
     */
    public function setPriceKeyRef($priceKeyRef)
    {
        $this->_priceKeyRef = $priceKeyRef;
    }

    /**
     * Возвращает ключ-ссылку на модификатор прайса
     *
     * @return mixed
     */
    public function getTicketModifiersRef()
    {
        return $this->_ticketModifiersRef;
    }

    /**
     * Устанавливает ключ-ссылку на модификатор прайса
     *
     * @param mixed $ticketModifiersRef
     */
    public function setTicketModifiersRef($ticketModifiersRef)
    {
        $this->_ticketModifiersRef = $ticketModifiersRef;
    }
}