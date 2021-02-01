<?php

namespace ReservationKit\src\Modules\Galileo\Model\Entity;

use ReservationKit\src\Modules\Avia\Model\Entity\Segment as AviaSegment;

class Segment extends AviaSegment
{
    /**
     * @var array
     */
    private $_codeshareInfo = array();

    /**
     * @var boolean
     */
    private $_needConnectionToNextSegment = false;

    private $_travelTime;
    private $_availabilityDisplayType;
    private $_optionalServicesIndicator;
    private $_changeOfPlane;
    private $_airAvailInfo;

    /**
     * @return array
     */
    public function getCodeshareInfo()
    {
        return $this->_codeshareInfo;
    }

    /**
     * @param array $codeshareInfo
     */
    public function setCodeshareInfo($codeshareInfo)
    {
        $this->_codeshareInfo = $codeshareInfo;
    }

    /**
     * @param $operatingCarrier
     * @param $codeshareInfoText
     */
    public function addCodeshareInfo($operatingCarrier, $codeshareInfoText)
    {
        $this->_codeshareInfo[$operatingCarrier] = $codeshareInfoText;
    }

    /**
     * Проверяет, необходимо ли подключение к следующему сегменту?
     * 
     * @return boolean
     */
    public function isNeedConnectionToNextSegment()
    {
        return $this->_needConnectionToNextSegment;
    }

    /**
     * Устанавливает, необходимо ли подключение к следующему сегменту
     * 
     * @param boolean $isNeedConnectionToNextSegment
     */
    public function setNeedConnectionToNextSegment($isNeedConnectionToNextSegment)
    {
        $this->_needConnectionToNextSegment = $isNeedConnectionToNextSegment;
    }

    /**
     * @return mixed
     */
    public function getTravelTime()
    {
        return $this->_travelTime;
    }

    /**
     * @param mixed $travelTime
     */
    public function setTravelTime($travelTime)
    {
        $this->_travelTime = $travelTime;
    }

    /**
     * @return mixed
     */
    public function getAvailabilityDisplayType()
    {
        return $this->_availabilityDisplayType;
    }

    /**
     * @param mixed $availabilityDisplayType
     */
    public function setAvailabilityDisplayType($availabilityDisplayType)
    {
        $this->_availabilityDisplayType = $availabilityDisplayType;
    }

    /**
     * @return mixed
     */
    public function getOptionalServicesIndicator()
    {
        return $this->_optionalServicesIndicator;
    }

    /**
     * @param mixed $optionalServicesIndicator
     */
    public function setOptionalServicesIndicator($optionalServicesIndicator)
    {
        $this->_optionalServicesIndicator = $optionalServicesIndicator;
    }

    /**
     * @return mixed
     */
    public function getChangeOfPlane()
    {
        return $this->_changeOfPlane;
    }

    /**
     * @param mixed $changeOfPlane
     */
    public function setChangeOfPlane($changeOfPlane)
    {
        $this->_changeOfPlane = $changeOfPlane;
    }

    /**
     * @return BookingCodeInfo[]
     */
    public function getAirAvailInfo()
    {
        return $this->_airAvailInfo;
    }

    /**
     * @param mixed $airAvailInfo
     */
    public function setAirAvailInfo($airAvailInfo)
    {
        $this->_airAvailInfo = $airAvailInfo;
    }
}