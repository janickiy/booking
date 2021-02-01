<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment;

class Term extends XmlElement
{
    /**
     * @param Segment $segment
     * @param null $segmentNum
     */
    public function __construct(Segment $segment, $segmentNum = null)
    {
        parent::__construct('Term', array('ObjectKey' => 'T' . ($segmentNum + 1), 'refs' => 'SEG' . ($segmentNum + 1)),
            new XmlElement('AvailablePeriod', array(), array(
                new XmlElement('Earliest', array(), '', 'ns1', true),
                new XmlElement('Latest', array(), '', 'ns1', true),
            ), 'ns1')
        , 'ns1');
    }
}