<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price;

class AirPricingInfoRef extends XmlElement
{
    /**
     * @param Price $price
     */
    public function __construct(Price $price)
    {
        $AirPricingInfoRef = new XmlElement('AirPricingInfoRef', array('Key' => $price->getKey()), null, 'air');

        parent::__construct(null, array(), $AirPricingInfoRef);
    }
}