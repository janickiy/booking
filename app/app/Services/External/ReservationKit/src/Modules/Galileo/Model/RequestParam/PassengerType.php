<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Passenger;

class PassengerType extends XmlElement
{
    /**
     * Праметр PassengerType должен соответствовать параметру FareInfo в запросе,
     * поэтому тип пассажира берется из прайсов, а не из списка пассажиров
     *
     * @param Passenger $passenger
     */
    public function __construct(Passenger $passenger)
    {
        $attributesPassengerType = array(
            'BookingTravelerRef' => $passenger->getKey(),
            'Code'               => $passenger->getType()
        );

        if ($passenger->isAgeRequired()) {
            //$attributesPassengerType['Age'] = str_pad($passenger->getAge(), 2, '0', STR_PAD_LEFT);
            switch ($passenger->getType()) {
                case 'CHD':
                    $attributesPassengerType['Age'] = '8';
                    break;
                case 'INF':
                    $attributesPassengerType['Age'] = '1';
                    break;
                default:
                    break;
            }
        }

        $PassengerType = new XmlElement('PassengerType', $attributesPassengerType, null, 'air');

        parent::__construct(null, array(), $PassengerType);
    }
}