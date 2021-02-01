<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity\Search\Settings;

class Rules
{
    private $_data;

    /**
     * @param string $rules JSON-формат
     */
    public function __construct($rules)
    {
        if (\RK_Core_Helper_String::isJson($rules)) {
            $rulesArray = json_decode($rules);
            
            foreach ($rulesArray as $key => $rule) {
                $this->set($key, $rule);
            }
        }
    }

    /**
     * Возвращает правило по ключу
     *
     * @param $key
     * @return null
     */
    public function get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Устанавливает значение
     * 
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Возвращает все правила
     *
     * @param array
     */
    public function getAll()
    {
        return $this->_data;
    }
}