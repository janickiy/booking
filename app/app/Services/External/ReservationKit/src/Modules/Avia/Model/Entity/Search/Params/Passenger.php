<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity\Search\Params;

/**
 * Описание пассажира для поиска
 *
 * Содержит информацию о типе и количестве пассжиров этого типа
 */
class Passenger
{
    /**
     * Тип пассажира (ADT, CHD, INF, INS)
     *
     * @var string
     */
    private $_type;

    /**
     * Количество пассажиров
     *
     * @var int
     */
    private $_count;

    public function __construct($type, $count = 1)
    {
        $this->setType((string) $type);
        $this->setCount((int) $count);
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
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->_count = $count;
    }
}