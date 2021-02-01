<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;

class TicketingModifiers extends XmlElement
{
    /**
     * @param GalileoBooking $booking
     */
    public function __construct(GalileoBooking $booking)
    {
        // Создание параметров TMU
        $elementEndorsement = HelperRequest::getListRequestParam('TicketEndorsement', $booking->getPassengers());

        // Создание параметров комиссии
        $elementCommission = null;
        $bookingCommission = $booking->getCommission();
        if (isset($bookingCommission[0], $bookingCommission[1])) {
            $valueCommission = $bookingCommission[0];
            $typeCommission = $bookingCommission[1];

            $commissionAttributes = array(
                'Key'   => createBase64UUID(),
                'Level' => 'Fare',
            );

            if ($typeCommission === 'RUB') {
                $commissionAttributes['Type'] = 'Flat';
                $commissionAttributes['Amount'] = (int) $valueCommission;
            }

            if ($typeCommission === '%') {
                $commissionAttributes['Type'] = 'PercentBase';
                $commissionAttributes['Percentage'] = number_format((float) $valueCommission, 2);
            }

            $elementCommission = array();
            $elementCommission[] = new XmlElement('Commission', $commissionAttributes, null, 'com');
        }

        // Содержимое нода TicketingModifiers
        $contentTicketingModifiers = array_merge(
            $elementCommission,
            $elementEndorsement
        );

        $TicketingModifiers = new XmlElement('TicketingModifiers', array(), $contentTicketingModifiers, 'air');

        //$TicketingModifiers = HelperRequest::getListRequestParam('TicketingModifiers', array(), $contentTicketingModifiers, 'air');

        parent::__construct(null, array(), $TicketingModifiers);
    }
}