<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\DB\Exception;
use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Requisites;

class AirPricingTicketingModifiers extends XmlElement
{
    public function __construct(GalileoBooking $booking)
    {
        $AirPricingTicketingModifiersList = array();

        $passengers = $booking->getPassengers();
        foreach ($passengers as $passenger) {
            $TicketingModifiersContent = array();

            if ($booking->isRussianCarrierOnly()) {
                // TMU
                $attributesTicketEndorsement = array(
                    'Value' => 'PSPT' . \RK_Core_Helper_String::translit($passenger->getDocNumber())
                );

                $TicketingModifiersContent[] = new XmlElement('TicketEndorsement', $attributesTicketEndorsement, null, 'air');
            }

            if ($booking->getValidatingCompany() === 'LH' && $booking->getTriPartyAgreementByNum(0)) {
                if ($booking->getTriPartyAgreementByNum(0)->getTourCode()) {
                    $tourCode = $booking->getTriPartyAgreementByNum(0)->getTourCode();
                    $attributesTicketEndorsement = array(
                        'Value' => 'TC' . $tourCode
                    );

                    $TicketingModifiersContent[] = new XmlElement('TicketEndorsement', $attributesTicketEndorsement, null, 'air');
                }
            }

            // Создание параметров комиссии
            if (!$booking->hasTourCodeInPrices() && Requisites::getInstance()->getRules()->getSearchPCC() !== '39NE') {
                $commissionKeyRef = createBase64UUID();
                $bookingCommission = $booking->getCommission();
                if (isset($bookingCommission[0], $bookingCommission[1])) {
                    $valueCommission = $bookingCommission[0];
                    $typeCommission = $bookingCommission[1];

                    $commissionAttributes = array(
                        'Key'   => $commissionKeyRef,
                        'Level' => 'Fare',
                    );

                    if ($typeCommission === '%') {
                        $commissionAttributes['Type'] = 'PercentBase';
                        $commissionAttributes['Percentage'] = number_format((float) $valueCommission, 2);

                    } else {
                        $commissionAttributes['Type'] = 'Flat';
                        $commissionAttributes['Amount'] = (float) $valueCommission;
                    }

                } else {
                    throw new Exception('Не проставлена комиссия в брони');
                }

                $TicketingModifiersContent[] = new XmlElement('Commission', $commissionAttributes, null, 'com');
            }

            // Создание нода
            $AirPricingTicketingModifiersList[] = new XmlElement('UniversalModifyCmd', array('Key' => createBase64UUID()),
                new XmlElement('AirAdd', array('ReservationLocatorCode' => $booking->getLocatorAirReservation()),

                    new XmlElement('AirPricingTicketingModifiers', array(), array(
                        new XmlElement('AirPricingInfoRef', array('Key' => $passenger->getPriceKeyRef()), null, 'air'),
                        new XmlElement('TicketingModifiers', array('PlatingCarrier' => $booking->getValidatingCompany()),
                            $TicketingModifiersContent
                        , 'air', true)
                    ), 'air'),

                'univ'),
            'univ');
        }

        parent::__construct(null, array(), $AirPricingTicketingModifiersList);
    }
}