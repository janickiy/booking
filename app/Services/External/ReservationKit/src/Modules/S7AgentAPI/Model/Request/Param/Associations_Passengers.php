<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Passenger as S7AgentPassenger;

class Associations_Passengers extends XmlElement
{
    public function __construct(array $passengersList)
    {
        // SH1 SH2 SH3
        $passengerReferences = '';
        foreach ($passengersList as $passengerNum => $passenger) {
            $rphNum = $passengerNum + 1;
            
            if ($passenger instanceof S7AgentPassenger) {
                $rphNum = $passenger->getRPH();
            }

            $passengerReferences .= 'SH' . $rphNum . ' ';
        }
        $passengerReferences = trim($passengerReferences);

        parent::__construct('Associations', array(),
            new XmlElement('Passengers', array(),
                new XmlElement('PassengerReferences', array(), $passengerReferences, 'ns1')
            , 'ns1')
        , 'ns1');
    }
}