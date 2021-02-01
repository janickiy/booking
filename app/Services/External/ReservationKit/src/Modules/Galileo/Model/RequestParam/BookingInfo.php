<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\BookingInfo as GalileoBookingInfo;

class BookingInfo extends XmlElement
{
    /**
     * @param GalileoBookingInfo $bookingInfo
     */
    public function __construct(GalileoBookingInfo $bookingInfo)
    {
        $attributesBookingInfo = array(
            'BookingCode' => $bookingInfo->getBookingCode(),  //'Q',
            'CabinClass'  => $bookingInfo->getCabinClass(),   //'Economy',
            'FareInfoRef' => $bookingInfo->getFareInfoRef(),  //'bKZf58eKRSGdhdkYa6P8+g==',
            'SegmentRef'  => $bookingInfo->getSegmentRef()    //'WOMIafkiRkSvAyJ/EI5AUw=='
        );

        if ($bookingInfo->getHostTokenRef()) {
            $attributesBookingInfo['HostTokenRef'] = $bookingInfo->getHostTokenRef();
        }

        $BookingInfo = new XmlElement('BookingInfo', $attributesBookingInfo, null, 'air');

        parent::__construct(null, array(), $BookingInfo);
    }
}