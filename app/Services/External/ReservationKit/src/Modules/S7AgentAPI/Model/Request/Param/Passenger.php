<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger as SearchPassenger;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Passenger as BookingPassenger;

class Passenger extends XmlElement
{
    /**
     * @param SearchPassenger|BookingPassenger $passenger
     * @param null $passengerNum
     */
    public function __construct($passenger, $passengerNum = null)
    {
        if ($passenger instanceof SearchPassenger) {
            parent::__construct('Passenger', array('ObjectKey' => 'SH' . ($passengerNum + 1)),
                array(
                    new XmlElement('PTC', array('Quantity' => $passenger->getCount()), $passenger->getType(), 'ns1'),
                    new XmlElement('Name', array(),
                        new XmlElement('Surname', array(), null, 'ns1', true)
                    , 'ns1')
                )
            , 'ns1');
        }

        if ($passenger instanceof BookingPassenger) {
            parent::__construct('Passenger', array('ObjectKey' => 'SH' . $passenger->getRPH()),
                array(
                    new XmlElement('PTC', array('Quantity' => '1'), $passenger->getType(), 'ns1'),
                    new XmlElement('Name', array(),
                        new XmlElement('Surname', array(), null, 'ns1', true)
                        , 'ns1')
                )
                , 'ns1');
        }
    }
}