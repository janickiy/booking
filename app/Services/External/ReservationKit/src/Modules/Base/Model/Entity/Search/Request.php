<?php

class RK_Base_Entity_Search_Request
{
    /**
     * Система бронирования
     *
     * @var string
     */
    protected $_system;

    /**
     * @var array
     */
    private $_options;

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
     * Возвраещает список установленных дополнительных параметров
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Возвраещает установленный параметр по ключу
     *
     * @param string $key
     * @return mixed
     */
    public function getOptionByKey($key)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : null;
    }

    /**
     * Установка дополнительных параметров
     *
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * Добавление дополнительного параметра
     *
     * @param string $key
     * @param mixed $value
     */
    public function addOption($key, $value)
    {
        $this->_options[$key] = $value;
    }
}