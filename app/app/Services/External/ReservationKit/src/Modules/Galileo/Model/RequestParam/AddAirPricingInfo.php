<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;

class AddAirPricingInfo extends XmlElement
{
    /**
     * TODO тело можно заменить на AirPricingInfo тут добавляемт обертка AirAdd
     *
     * @param \RK_Avia_Entity_Booking $booking
     */
    public function __construct(\RK_Avia_Entity_Booking $booking)
    {
        $airPricingInfoXmlList = array();
        $hostTokensXmlList = array();

        $passengers = $booking->getPassengers();

        foreach ($passengers as $numPassenger => $passenger) {
            $passengerType = $passenger->getType();
            $price = $booking->getPriceByTypePassenger($passengerType);

            $priceKeyRef = createBase64UUID();
            $passenger->setPricekeyRef($priceKeyRef);

            $attributesAirPrice = array(
                'Key'                   => $priceKeyRef,
                'TotalPrice'            => $price->getTotalFare()->getAmount(),
                'BasePrice'             => $price->getBaseFare()->getAmount(),
                'ApproximateTotalPrice' => $price->getApproximateTotalPrice()->getAmount(),
                'ApproximateBasePrice'  => $price->getApproximateBasePrice()->getAmount(),
                'ApproximateTaxes'      => $price->getApproximateTaxes()->getAmount(),
                'Taxes'                 => $price->getTaxesSum()->getAmount(),
                'LatestTicketingTime'   => $price->getTicketTimelimit()->formatTo(\RK_Core_Date::DATE_FORMAT_ISO_8601),
                'PricingMethod'         => $price->getPricingMethod(),
                'IncludesVAT'           => $price->getIncludesVAT(),
                'ProviderCode'          => '1G',
                'AirPricingInfoGroup'   => (string) ($numPassenger + 1)
            );

            if ($price->getEquivFare()) {
                $attributesAirPrice['EquivalentBasePrice'] = $price->getEquivFare()->getAmount();
            }

            $airPricingInfo = new XmlElement('AirPricingInfo', $attributesAirPrice, array_merge(
                HelperRequest::getListRequestParam('FareInfo', $booking->getPriceByTypePassenger($passengerType)->getFareInfo()),
                HelperRequest::getListRequestParam('BookingInfo', $booking->getPriceByTypePassenger($passengerType)->getBookingInfoList()),
                HelperRequest::getListRequestParam('TaxInfo', $price->getTaxes()),

                array(
                    HelperRequest::buildRequestParam('PassengerType', $passenger),

                    new XmlElement('AirPricingModifiers', array(),
                        HelperRequest::buildRequestParam('AccountCodes', $booking),
                    'air'),

                    new XmlElement('BaggageAllowances', array(),
                        HelperRequest::getListRequestParam('BaggageAllowanceInfo', $price->getBaggageAllowances()),
                    'air')
                )),
            'air');

            // Список новых тарифов
            $airPricingInfoXmlList[] = $airPricingInfo;

            /*
            // Список новых тарифов
            $airPricingInfoXmlList[] = new XmlElement('UniversalModifyCmd', array('Key' => createBase64UUID()), array(
                new XmlElement('AirAdd', array('ReservationLocatorCode' => $booking->getLocator()), $airPricingInfo, 'univ')
            ), 'univ');
            */

            // Список хостов к этим тарифам
            if ($price->getHostTokens()) {
                $hostTokensXmlList = new XmlElement('UniversalModifyCmd', array('Key' => createBase64UUID()), array(
                    new XmlElement('AirAdd', array('ReservationLocatorCode' => $booking->getLocatorAirReservation()),
                        HelperRequest::getListRequestParam('HostToken', $price->getHostTokens()),
                    'univ')
                ), 'univ');
            }
        }

        $airPricingInfoXmlList2 = new XmlElement('UniversalModifyCmd', array('Key' => createBase64UUID()), array(
            new XmlElement('AirAdd', array('ReservationLocatorCode' => $booking->getLocatorAirReservation()), $airPricingInfoXmlList, 'univ')
        ), 'univ');

/*
        if ($booking->getHostTokensForPrice()) {
            $airPricingInfoList = array_merge(
                $airPricingInfoList, HelperRequest::getListRequestParam('HostToken', $booking->getHostTokensForPrice())
            );
        }
*/

        parent::__construct(null, array(), array($airPricingInfoXmlList2, $hostTokensXmlList));
    }
}