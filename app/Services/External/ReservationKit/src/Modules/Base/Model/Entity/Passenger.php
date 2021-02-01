<?php

/**
 * Общий класс пассажира
 */
abstract class RK_Base_Entity_Passenger
{
    /**
     * Тип пассажира:
     *   ADT Врослые
     *   CLD Дети
     *   INF Младенцы
     *   INS Младенцы с местом
     *
     * @var string
     */
    protected $_type;

    /**
     * Данные пассажира
     *
     * @var array
     */
    protected $_values = array();

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

    abstract public function validate($name, $value);

    /**
     * Обработка данных
     *
     * @param string $name
     * @param $value
     * @return mixed
     */
    abstract public function processing($name, $value);

    /**
     * Устанавливает свойство пассажира
     *
     * @param string $name
     * @param mixed $value
     *
     * @throws RK_Base_Exception
     */
    public function setValue($name, $value)
    {
        if ($this->validate($name, $value)) {
            $this->_values[$name] = $this->processing($name, $value);
        } else {
            //throw new RK_Base_Exception('Invalid value for ' . $name);
        }
    }

    /**
     * Возвращает свойство пассажира
     *
     * @param string $name
     * @return mixed Возвращает null, если значения нет
     */
    public function getValue($name)
    {
        if (isset($this->_values[$name])) {
            return $this->_values[$name];
        }

        return null;
    }

    /**
     * Возвращает все свойства пассажира
     *
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }
}