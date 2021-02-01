<?php

namespace ReservationKit\src\Modules\Avia\Model\Entity\Search;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Settings\Rules;

class Settings
{
    private $_id;
    
    private $_name;
    
    private $_system;
    
    private $_rules;
    
    private $_isActive;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return mixed
     */
    public function getSystem()
    {
        return $this->_system;
    }

    /**
     * @param mixed $system
     */
    public function setSystem($system)
    {
        $this->_system = $system;
    }

    /**
     * @return Rules
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * @param mixed $rules
     */
    public function setRules($rules)
    {
        $this->_rules = $rules;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->_isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive)
    {
        $this->_isActive = $isActive;
    }
}