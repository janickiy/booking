<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class SearchRequest extends \RK_Avia_Entity_Search_Request
{
    /**
     * @var string
     */
    private $_nextResultReference;

    /**
     * @return string
     */
    public function getNextResultReference()
    {
        return $this->_nextResultReference;
    }

    /**
     * @param string $nextResultReference
     */
    public function setNextResultReference($nextResultReference)
    {
        $this->_nextResultReference = $nextResultReference;
    }
}