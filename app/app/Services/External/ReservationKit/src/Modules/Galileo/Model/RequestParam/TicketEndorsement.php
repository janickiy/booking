<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Passenger as GalileoPassenger;

class TicketEndorsement extends XmlElement
{
    /**
     * Пример для первго пассажира, <air:TicketEndorsement Value="EBPSPT1234567"/>
     *
     * @param GalileoPassenger $passenger
     */
    public function __construct(GalileoPassenger $passenger)
    {
        $attributesTicketEndorsement = array(
            'Value' => 'PSPT' . \RK_Core_Helper_String::translit($passenger->getDocNumber())
        );

        $TicketEndorsement = new XmlElement('TicketEndorsement', $attributesTicketEndorsement, array(), 'air');

        parent::__construct(null, array(), $TicketEndorsement);
    }
}