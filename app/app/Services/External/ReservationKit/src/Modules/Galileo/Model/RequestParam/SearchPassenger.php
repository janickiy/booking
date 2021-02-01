<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;

class SearchPassenger extends XmlElement
{
    /**
     * @param Passenger $passenger
     */
    public function __construct(Passenger $passenger)
    {
		$key = createBase64UUID();
        $attributesSearchPassenger = array
		(
			//'Key' => $key,
			'BookingTravelerRef' => $key,
			'Code' => $passenger->getType()
        );

        // Для детей и младенцев добавлятся атрибут Age
        if ($passenger->getType() === 'CHD') {
            $attributesSearchPassenger['Age'] = '08';
        }
        if ($passenger->getType() === 'INF') {
            $attributesSearchPassenger['Age'] = '01';
        }

        parent::__construct('SearchPassenger', $attributesSearchPassenger, null, 'com');
    }
}