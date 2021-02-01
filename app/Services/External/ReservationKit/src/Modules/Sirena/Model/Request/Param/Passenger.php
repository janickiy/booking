<?php

namespace ReservationKit\src\Modules\Sirena\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger as SearchPassenger;
use ReservationKit\src\Modules\Sirena\Model\Entity\Passenger as BookingPassenger;

class Passenger extends XmlElement
{
    /**
     * @param SearchPassenger|BookingPassenger $passenger
     * @param null $passengerNum
     */
    public function __construct($passenger, $passengerNum = null)
    {
        $passengerType = str_replace(['ADT', 'CHD', 'INF'], ['AAT', 'CHILD', 'INFANT'], $passenger->getType());

        if ($passenger instanceof SearchPassenger) {
            parent::__construct('passenger', array(), array(
                new XmlElement('code', array(), $passengerType),
                new XmlElement('count', array(), $passenger->getCount())
            ));
        }

        if ($passenger instanceof BookingPassenger) {
            parent::__construct('passenger', array(), array(
                new XmlElement('lastname', array(), $passenger->getLastname()),
                new XmlElement('firstname', array(), $passenger->getFirstname()),
                new XmlElement('birthdate', array(), $passenger->getBorndate()),
                new XmlElement('sex', array(), $passenger->getGender()),
                new XmlElement('category', array(), $passengerType),
                new XmlElement('doccode', array(), $passenger->getDocType()),
                new XmlElement('doc', array(), $passenger->getDocNumber()),
                new XmlElement('doc_country', array(), $passenger->getDocCountry()),
                new XmlElement('nationality', array(), $passenger->getNationality()),
                //new XmlElement('residence', array(), $passenger->getNationality()),
                //new XmlElement('phone', array('type' => 'mobile', 'comment' => 'ЗВОНИТЬ ПОСЛЕ 19:00'), $passenger->getPhone()),
                //new XmlElement('phone', array('type' => 'work'), $passenger->getPhone()),
            ));
        }
    }
}