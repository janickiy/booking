<?php

namespace ReservationKit\src\Modules\Avia\Model\Abstracts;

use ReservationKit\src\Modules\Avia\Model\Interfaces\IService;

abstract class Service implements IService
{
    private $_result;

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->_result = $result;
    }
}