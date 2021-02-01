<?php

namespace ReservationKit\src\Modules\Sirena\Model\Request;

use ReservationKit\src\Component\XML\XmlElement;

use ReservationKit\src\Modules\Sirena\Model\Request;

class Booking extends Request
{
    /**
     * @var array
     */
    private $_params;

    /**
     * @param RK_Avia_Entity_Booking $booking
     * @param $phone
     */
    public function __construct(RK_Avia_Entity_Booking $booking, $phone)
    {
        // Установка параметров для бронировния
        $this->addParam('segments', $booking->getSegments());
        $this->addParam('passengers', $booking->getPassengers());
        $this->addParam('phone', $phone);

        // Установка поискового запроса
        $this->setRequest($this->buildRequest());
    }

    /**
     * @param $key
     * @param $value
     */
    public function addParam($key, $value)
    {
        $this->_params[(string)$key] = $value;
    }

    public function buildRequest()
    {
        $SegmentsParams = $this->getSegmentParams();
        $PassengerParams = $this->getPassengerParams();

        $request = '<?xml version="1.0" encoding="utf-8"?>
            <sirena>
                <query>
                  <booking>
                    ' . $SegmentsParams . '
                    ' . $PassengerParams . '
                    <contacts>
                        <contact>' . $this->getParam("phone") . '</contact>
                    </contacts>
                    <request_params>
                        <tick_ser>'.iconv("cp1251", "utf-8", "ЭБМ").'</tick_ser>
                    </request_params>
                    <answer_params>
                        <show_tts>true</show_tts>
                        <show_upt_rec>true</show_upt_rec>
                        <add_remarks>true</add_remarks>
                        <lang>en</lang>
                    </answer_params>
                  </booking>
                </query>
              </sirena>';

        return new SimpleXMLElement($request);
    }

    function getSegmentParams()
    {
        $arSegments = array();

        /* @var RK_Avia_Entity_Segment $segment */

        foreach ($this->getSegments() as $segmentNum => $segment) {

            $segmentParam = "<segment>";
            //$segmentParam .= "<company>" . (string)$segment->getOperationCompanyCode() . "</company>";
            $segmentParam .= "<company>" . (string)$segment->getMarketingCompanyCode() . "</company>";
            $segmentParam .= "<flight>" . (string)$segment->getFlightNumber() . "</flight>";
            $segmentParam .= "<departure>" . (string)$segment->getDepartureCode() . "</departure>";
            $segmentParam .= "<arrival>" . (string)$segment->getArrivalCode() . "</arrival>";
            $segmentParam .= "<date>" . (string)$segment->getDepartureDate()->formatTo("d.m.Y")->getValue() . "</date>";
            $segmentParam .= "<subclass>" . (string)$segment->getBaseClass() . "</subclass>";
            $segmentParam .= "</segment>";


            $arSegments[] = $segmentParam;

        }

        return implode("", $arSegments);
    }


    // TODO вынести в отдельный класс с генерацией параметров

    private function getSegments()
    {
        return $this->getParam('segments');
    }

    /**
     * @param $key
     * @return null
     */
    public function getParam($key)
    {
        return isset($this->_params[(string)$key]) ? $this->_params[(string)$key] : null;
    }

    function getPassengerParams()
    {
        $arPassengers = array();

        /* @var RK_Avia_Entity_Passenger $passenger */

        foreach ($this->getPassengers() as $passNum => $passenger) {

            $passengerParam = "<passenger>";
            $passengerParam .= "<surname>" . iconv("cp1251", "utf-8", (string)$passenger->getLastname()) . "</surname>";
            $passengerParam .= "<name>" . iconv("cp1251", "utf-8", $passenger->getFirstname() . " " . $passenger->getMiddlename()) . "</name>";
            $passengerParam .= "<age>" . $passenger->getBorndate('d.m.Y') . "</age>";
            $passengerParam .= "<sex>" . (($passenger->getGender() == "M") ? "male" : "female") . "</sex>";
            $passengerParam .= "<category>" . $passenger->getType() . "</category>";
            $passengerParam .= "<doccode>" . $passenger->getDocType() . "</doccode>";
            $passengerParam .= "<doc>" . iconv("cp1251", "utf-8", $passenger->getDocNumber()) . "</doc>";
            $passengerParam .= "<doc_country>" . $passenger->getDocCountry() . "</doc_country>";
            $passengerParam .= "<nationality>" . $passenger->getNationality() . "</nationality>";

            if ($passenger->getDocExpired()) {
                $passengerParam .= "<pspexpire>" . $passenger->getDocExpired() . "</pspexpire>";
            }


            // ???
            //$passengerParam.= "<residence>".$passenger->getNationality()."</residence>";
            $passengerParam .= "<phone type='mobile'>" . $passenger->getPhone() . "</phone>";
//           $passengerParam.= "<phone type='work'>74957654321</phone>";
            $passengerParam .= "</passenger>";


            $arPassengers[] = $passengerParam;

        }

        return implode("", $arPassengers);
    }

    private function getPassengers()
    {
        return $this->getParam('passengers');
    }

    function getContactParams()
    {
        $arContacts = array();

        /* @var RK_Avia_Entity_Passenger $passenger */

        foreach ($this->getPassengers() as $passNum => $passenger) {
            if ($passenger->getPhone()) {
                $arContacts[] = "<contact>" . $passenger->getPhone() . "</contact>";
            }
        }

        return "<contacts>" . implode("", $arContacts) . "</contacts>";
    }

    public function getRequestName()
    {
        return 'booking';
    }

    public function getRequestAttributes()
    {
        return array();
    }
}