<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement;

class AccountCodes extends XmlElement
{
    /**
     * Элемент содержащий туркод (трехстронний договор, 3D fares)
     *
     * @param GalileoBooking|\RK_Avia_Entity_Search_Request $searchRequestOrBooking
     */
    public function __construct($searchRequestOrBooking)
    {
        $AccountCodes = null;

        if ($agreement = $searchRequestOrBooking->getTriPartyAgreementByNum(0)) {
            $code = $agreement->getAccountCode();

            if (empty($code)) {
                return null;
            }

            $AccountCodes = new XmlElement('AccountCodes', array(),
                new XmlElement('AccountCode', array('Code' => $code, 'ProviderCode' => '1G', 'SupplierCode' => $agreement->getCarrier()), null, 'com'),
            'air');

            parent::__construct(null, array(), $AccountCodes);
        }

        return null;
    }
}