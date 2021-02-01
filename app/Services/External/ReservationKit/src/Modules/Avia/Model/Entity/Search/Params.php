<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity\Search;

class Params extends \RK_Base_Entity_Search_Request
{
    /**
     * Тип маршрута
     *
     * OW - в одну сторону
     * RW - туда и обратно
     * MW - сложный маршрут
     *
     * @var string
     */
    protected $_type = null;

    /**
     * Класс перелета
     *
     * ECONOMY - эконом
     * BUSINESS - бизнес
     * FIRST - первый
     * ANY - любой
     *
     * @var string
     */
    protected $_classType = null;

    /**
     * Массив сегментов в маршруте
     *
     * - 'Номер сегмента' => 'Код_сегмента'
     */
    protected $_segments = array();

    /**
     * Ассоциативный массив с информацией о пассажирах (тип, количество)
     *
     * Ключи в массиве это типы пассжиров:
     * Допустимые ключи:
     * - 'ADT' 'Врослые'
     * - 'CLD' 'Дети'
     * - 'INF' 'Младенцы'
     * - 'INS' 'Младенцы с местом'
     * Значения в массиве это количество пассажиров.
     *
     * @see RK_Main_Passenger
     * @return array
     */
    protected $_passengers = array();

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
        return $this->_classType;
    }

    /**
     * @param string $classType
     */
    public function setClassType($classType)
    {
        $this->_classType = $classType;
    }

    /**
     * @return mixed
     */
    public function getPassengers()
    {
        return $this->_passengers;
    }

    /**
     * @param mixed $passengers
     */
    public function setPassengers(array $passengers)
    {
        $this->_passengers = array();
        foreach ($passengers as $passenger) {
            $this->addPassenger($passenger['type'], 1);
        }
    }

    /**
     * Добавляет нового пассажира
     *
     * @param string $type Тип пассажира
     * @param int $count Количество
     *
     * @see RK_Base_Entity_Passenger
     */
    public function addPassenger($type, $count)
    {
        if (isset($this->_passengers[$type])) {
            $this->_passengers[$type] += $count;
        } else {
            $this->_passengers[$type] = $count;
        }
    }

    /**
     * Возвращает массив сегментов перелёта
     *
     * @return RK_Avia_Entity_Search_Request_Segment[]
     */
    public function getSegments()
    {
        return $this->_segments;
    }
    /**
     * Устанавливаем массив сегментов
     *
     * @param RK_Avia_Entity_Search_Request_Segment[] $segments
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
     * @param RK_Avia_Entity_Search_Request_Segment $segment
     */
    public function addSegment(/*RK_Avia_Entity_Search_Request_Segment*/ $segment)
    {
        $this->_segments[] = $segment;
    }
}