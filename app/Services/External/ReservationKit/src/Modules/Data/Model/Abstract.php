<?php

/**
 * Базовый обьект данных
 *
 * Представляет собой коллекцию данных
 */
class RK_Data_Abstract
{
    protected $_id;

    protected $_properties = array();

    protected $_columns = array();

    public function __construct()
    {
        $this->_columns = array_flip($this->_columns);

        foreach ($this->_columns as $columnName => $value) {
            $this->_columns[$columnName] = $columnName;
        }
    }

    /**
     * Устанавливает номер обьекта
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Возвращает номер обьекта
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Устанавливает значение свойства
     *
     * @param string $name
     * @param mixed $value
     */
    public function setProperty($name, $value)
    {
        if (isset($this->_columns[$name])) {
            $this->_properties[$name] = $value;
        }
    }

    /**
     * Возвращает свойство обьекта по имени
     *
     * @param string $name
     * @throws RK_Data_Exception
     * @return mixed
     */
    public function getProperty($name)
    {
        if (isset($this->_columns[$name])) {
            return $this->_properties[$name];
        }

        /*
        if (isset($this->_columns[RK_Data_Helper::getInstance()->serviceToColumn($name)])) {
            return $this->_properties[RK_Data_Helper::getInstance()->serviceToColumn($name)];
        }
        */

        throw new RK_Data_Exception('Invalid object property');
    }

    /**
     * Возвращает все свойства обьекта
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->_properties;
    }

    /**
     * Устанавливает все свойства обьекта
     */
    public function setProperties($properties)
    {
        $this->_properties = $properties;
    }

    /**
     * Возвращает названия свойств обьекта
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->_columns;
    }

    /**
     * Возвращает основное название обьекта (как правило англ.)
     *
     * @return string
     */
    public function getName()
    {
        return $this->getProperty('name');
    }

    /**
     * Возвращает описание обьекта
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getProperty('description');
    }
}