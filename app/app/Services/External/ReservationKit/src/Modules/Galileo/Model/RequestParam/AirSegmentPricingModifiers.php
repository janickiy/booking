<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;

class AirSegmentPricingModifiers extends XmlElement
{
    /**
     * @param Segment|GalileoSegment $segment
     */
    public function __construct(Segment $segment)
    {
        $xmlPermittedBookingCodes = null;

        if ($segment->getSubClass()) {
            $xmlPermittedBookingCodes = new XmlElement('PermittedBookingCodes', array(),
                new XmlElement('BookingCode', array('Code' => $segment->getSubClass()), null, 'air'),
            'air');
        }

        $attributesAirSegmentPricingModifiers = array(
            'AirSegmentRef' => $segment->getKey(),
            'CabinClass' => $segment->getTypeClass()
        );

        parent::__construct('AirSegmentPricingModifiers', $attributesAirSegmentPricingModifiers, $xmlPermittedBookingCodes, 'air');
    }
}