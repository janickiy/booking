<?php

class RK_Sirena_Param_Segment extends RK_Sirena_Param
{
    public function __construct(array $segments)
    {
        $this->addParam('segments', $segments);
    }

    public function getXML()
    {
        $ar_segments = array();

        /* @var RK_Avia_Entity_Search_Request_Segment $segment */

        foreach ($this->getSegments() as $segmentNum => $segment) {

            $originLocation         = $this->createXMLElement('departure', $segment->getDepartureCode());
            $destinationLocation    = $this->createXMLElement('arrival', $segment->getArrivalCode());
            $departureDateTime      = $this->createXMLElement('date', $segment->getDepartureDate());
            $timeFrom               = $this->createXMLElement('time_from', "0000");
            $timeTill               = $this->createXMLElement('time_till', "2359");
            $direct                 = $this->createXMLElement('direct',    $segment->isDirect()?"true":"false");
            $baseClass              = $this->createXMLElement('class', iconv("cp1251", "utf-8", $segment->getBaseClass()));

            $company = $flight = $subClass = "";
            if ($segment->getOperationCarrierCode()) {
                $company = $this->createXMLElement('company', $segment->getOperationCarrierCode());
            }
            if ($segment->getFlightNumber()) {
                $flight = $this->createXMLElement('flight', $segment->getFlightNumber());
            }
            if ($segment->getSubClass()) {
                $subClass = $this->createXMLElement('subclass', $segment->getSubClass());
            }

            $ar_segments[] = $this->createXMLElement('segment', $departureDateTime . $originLocation . $destinationLocation . $baseClass . $direct . $timeFrom . $timeTill . $company . $flight . $subClass);
        }

        return implode("", $ar_segments);
    }

    private function getSegments()
    {
        return $this->getParam('segments');
    }

}