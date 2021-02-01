<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;

class Traveler extends XmlElement
{
    /**
     * @param Passenger $passenger
     */
    public function __construct(Passenger $passenger)
    {
        parent::__construct('Traveler', array(),
            new XmlElement('AnonymousTraveler', array(),
                new XmlElement('PTC', array('Quantity' => $passenger->getCount()), $passenger->getType(), 'ns1')
            , 'ns1')
        , 'ns1');
    }
}