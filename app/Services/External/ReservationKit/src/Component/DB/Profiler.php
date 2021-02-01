<?php

namespace ReservationKit\src\Component\DB;

/**
 * Профайлер запросов к БД
 *
 * Фиксация выполняемые запросы и время их выполнения
 */
class Profiler
{
    /**
     * Список запросов
     *
     * @var array
     */
    private $_listQueries;

    public function __construct()
    {
        $this->_listQueries = array();
    }

    /**
     * Добавляет новый запрос в список
     *
     * @param string $sql
     * @param double $time
     */
    public function addQuery($sql, $time)
    {
        $this->_listQueries[] = array(
            'sql' => $sql,
            'time' => $time
        );
    }

    /**
     * Возвращает список запросов
     *
     * @return array
     */
    public function getListQueries()
    {
        return $this->_listQueries;
    }
}
