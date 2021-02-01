<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment;

class Endorsement extends XmlElement
{
    /**
     * @param $endorsement
     */
    public function __construct($endorsement)
    {
        parent::__construct('Endorsement', array(), $endorsement, 'ns1');
    }
}