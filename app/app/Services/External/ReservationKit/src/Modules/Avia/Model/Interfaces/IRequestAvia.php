<?php

namespace ReservationKit\src\Modules\Avia\Model\Interfaces;

// TODO на данный момент используется только для определения хелпера
interface IRequestAvia
{
    /**
     * Возвращает название GDS
     *
     * @return string
     */
    public static function getServiceName();

    /**
     * Возвращает массив соответсвия типа класса бронирования коду базового класса бронирования
     *
     * @return array
     */
    public static function getClassRefs();
}