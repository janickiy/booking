<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request as HelperRequest;

class AirPricingInfo extends XmlElement
{
    /**
     * @param \RK_Avia_Entity_Booking $booking
     * @throws \RK_Core_Exception
     * @throws \RK_Gabriel_Exception
     */
    public function __construct(\RK_Avia_Entity_Booking $booking, $discounts = array())
    {
        $airPricingInfoList = array();
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
                'Taxes'                 => ($price->isSetTaxes()) ? $price->getTaxesSum()->getAmount() : $price->getApproximateTaxes()->getAmount(),
                'LatestTicketingTime'   => $price->getTicketTimelimit()->formatTo(\RK_Core_Date::DATE_FORMAT_ISO_8601),
                'PricingMethod'         => $price->getPricingMethod(),
                'IncludesVAT'           => $price->getIncludesVAT(),
                'ProviderCode'          => '1G',
                'AirPricingInfoGroup'   => (string) ($numPassenger + 1)
            );

            if ($price->getEquivFare()) {
                $attributesAirPrice['EquivalentBasePrice'] = $price->getEquivFare()->getAmount();
            }
			
			$items = array(HelperRequest::buildRequestParam('AccountCodes', $booking));
			if ($discounts && \User::$holding_id == 30) $items = array_merge($items, $discounts);
					
            $airPricingInfo = new XmlElement('AirPricingInfo', $attributesAirPrice, array_merge(
                HelperRequest::getListRequestParam('FareInfo', $booking->getPriceByTypePassenger($passengerType)->getFareInfo()),
                HelperRequest::getListRequestParam('BookingInfo', $booking->getPriceByTypePassenger($passengerType)->getBookingInfoList()),
                HelperRequest::getListRequestParam('TaxInfo', $price->getTaxes()),

                array(
                    HelperRequest::buildRequestParam('PassengerType', $passenger),


                    new XmlElement('AirPricingModifiers', array(),
                        $items,
                    'air'),

                    new XmlElement('BaggageAllowances', array(),
                        HelperRequest::getListRequestParam('BaggageAllowanceInfo', $price->getBaggageAllowances()),
                    'air')
                )),
                'air');

            $airPricingInfoList[] = $airPricingInfo;
        }

        parent::__construct(null, array(), $airPricingInfoList);
    }
}