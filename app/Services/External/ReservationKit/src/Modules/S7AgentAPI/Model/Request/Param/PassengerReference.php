<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;

class PassengerReference extends XmlElement
{
    /**
     * @param array $passengers
     */
    public function __construct($passengers)
    {
        $PassengerReferenceContent = '';
        foreach ($passengers as $passengerNum => $passenger) {
            $PassengerReferenceContent .= 'SH' . ($passengerNum + 1) . ' ';
        }
        $PassengerReferenceContent = trim($PassengerReferenceContent);

        parent::__construct('PassengerReference', array(), $PassengerReferenceContent, 'ns1');
    }
}