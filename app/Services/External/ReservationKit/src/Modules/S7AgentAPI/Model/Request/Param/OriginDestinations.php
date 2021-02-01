<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Request\Param;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\S7AgentAPI\Model\Helper\Request as HelperRequest;

class OriginDestinations extends XmlElement
{
    /**
     * @param Segment[] $segments
     */
    public function __construct(array $segments)
    {
        // Распределение сегментов по плечам
        $waysList = array();
        foreach ($segments as $segment) {
            if (empty($waysList[$segment->getWayNumber()])) {
                $waysList[$segment->getWayNumber()] = array();
            }

            $waysList[(int) $segment->getWayNumber()][] = $segment;
        }

        $OriginDestinationList = array();
        foreach ($waysList as $waySegments) {
            $OriginDestinationList[] = new XmlElement('OriginDestination', array(), HelperRequest::getListRequestParam('Flight', $waySegments), 'ns1');
        }

        parent::__construct(null, array(), $OriginDestinationList, 'ns1');
    }
}