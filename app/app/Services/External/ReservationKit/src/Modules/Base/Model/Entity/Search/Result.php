<?php

/**
 * Результаты поиска
 */
class RK_Base_Search_Results
{
    protected $_results = array();

    public function __construct()
    {

    }

    public function addResult(RK_Base_Entity_Booking $item)
    {
        $this->_results[] = $item;
    }

    public function setResults(& $results)
    {
        $this->_results = $results;
    }

    public function & getResults()
    {
        return $this->_results;
    }

}

