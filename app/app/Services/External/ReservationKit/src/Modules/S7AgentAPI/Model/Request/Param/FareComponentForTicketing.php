<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment;

class FareComponentForTicketing extends XmlElement
{
    /**
     * @param Segment $segment
     * @param null $segmentNum
     */
    public function __construct(Segment $segment, $segmentNum = null, $options = null)
    {
        if ($segmentNum === 0) {
            return null;
        }

        $ticketDesignator = null;
        if (isset($options['type'], $options['values'])) {
            $ticketDesignator = new XmlElement('TicketDesig', array('Application' => $options['type']), $options['values'][$segmentNum], 'ns1');
        }

        parent::__construct('FareComponent', array('refs' => 'SEG' . ($segmentNum + 1), 'ObjectKey' => 'FC' . ($segmentNum + 1)), array(
            new XmlElement('FareBasis', array(),
                new XmlElement('FareBasisCode', array(),
                    new XmlElement('Code', array(), $segment->getFareCode(), 'ns1')
                , 'ns1')
            , 'ns1'),

            $ticketDesignator,

        ), 'ns1');
    }
}