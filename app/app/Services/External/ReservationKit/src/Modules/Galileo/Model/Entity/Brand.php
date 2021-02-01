<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

class Brand
{
    /**
     * @var string
     */
    private $_brandID;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var string
     */
    private $_carrier;

    /**
     * @var array
     */
    private $_titles = array();

    /**
     * @var array
     */
    private $_texts = array();

    /**
     * @return string
     */
    public function getBrandID()
    {
        return $this->_brandID;
    }

    /**
     * @param string $brandID
     */
    public function setBrandID($brandID)
    {
        $this->_brandID = $brandID;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getCarrier()
    {
        return $this->_carrier;
    }

    /**
     * @param string $carrier
     */
    public function setCarrier($carrier)
    {
        $this->_carrier = $carrier;
    }

    /**
     * @return array
     */
    public function getTitles()
    {
        return $this->_titles;
    }

    /**
     * @param array $titles
     */
    public function setTitles($titles)
    {
        $this->_titles = $titles;
    }

    /**
     * @param $type
     * @param $title
     */
    public function addTitle($type, $title)
    {
        $this->_titles[$type] = $title;
    }

    /**
     * @return array
     */
    public function getTexts()
    {
        return $this->_texts;
    }

    /**
     * @param array $texts
     */
    public function setTexts($texts)
    {
        $this->_texts = $texts;
    }

    /**
     * @param $type
     * @param $text
     */
    public function addText($type, $text)
    {
        $this->_texts[$type] = $text;
    }
}