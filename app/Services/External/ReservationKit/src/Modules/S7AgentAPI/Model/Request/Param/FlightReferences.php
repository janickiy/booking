<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;

class FlightReferences extends XmlElement
{
    /**
     * @param array $segments
     */
    public function __construct(array $segments)
    {
        $FlightReferencesContent = '';
        foreach ($segments as $segmentNum => $segment) {
            $FlightReferencesContent .= 'FL' . ($segmentNum + 1) . ' ';
        }
        $FlightReferencesContent = trim($FlightReferencesContent);

        parent::__construct('FlightReferences', array(), $FlightReferencesContent, 'ns1');
    }
}