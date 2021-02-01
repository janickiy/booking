<?php

/**
 * Класс для извлечения данных из БД об аэропортах
 */
class RK_Data_Source_Airport extends RK_Data_Abstract
{
    /**
     * Название таблицы аэропортов
     *
     * @var string
     */
    protected $_detailTable = 'static_airport_detail';

    /**
     * Столбцы таблицы, из которых извлекать данные
     *
     * @var array
     */
    protected $_extractColsValues = array(
        'ru_name', 'id', 'name', 'iata', 'hab', 'crt', 'country_id'
    );

    /**
     * @var RK_Data_Source_Airport
     */
    protected static $_instance;

    /**
     * @return RK_Data_Source_Airport
     */
    public static function getInstance()
    {
        return self::$_instance ? self::$_instance : (self::$_instance = new RK_Data_Source_Airport);
    }

    /**
     * Вставка значений
     *
     * @param $values
     * @return mixed
     */
    public function insert($values)
    {
        $values = array(
            'id' => NULL,
            'name' => @$values['name'],
            'en_name' => @$values['en_name'],
            'ru_name' => @$values['ru_name'],
            'iata' => @$values['iata'],
            'crt' => @$values['crt'],
            'hab' => @$values['hab'],
            'country_id' => @$values['country_id'],
            'lat' => strlen(@$values['lat']) ? (double) $values['lat'] : null,
            'lng' => strlen(@$values['lng']) ? (double) $values['lng'] : null,
            'www' => @$values['www'],
            'phones' => @$values['phones'],
            'time_zone' => @$values['time_zone'],
            'rating' => NULL,
        );

        $pointers = array_fill(0, count($values), '?');

        $sql = 'INSERT INTO ' . $this->_detailTable . ' VALUES(' . implode(',', $pointers) . ')';
        $this->getDB()->query( $sql, $values );
        $id = $this->getDB()->getLastInsertId();

        return $id;
    }
}