<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

class PricingResultHelper
{
    /**
     * @param $priceSolutions
     * @return array
     * @throws \RK_Core_Exception
     */
    public static function pricingResponseToJSON($priceSolutions)
    {
        $resultJSON = [];

        if (is_array($priceSolutions)) {
            $passengerPrices = [];

            foreach ($priceSolutions as $priceId => $prices) {
                $totalPrice = new \RK_Core_Money();

                /* @var \RK_Avia_Entity_Price $price */
                foreach ($prices as $typePassenger => $price) {
                    $passengerPrices[$typePassenger] = [
                        'typePassenger' => $price->getType(),
                        'baseAmount' => $price->getBaseFare()->getAmount('VALCUR'),
                        'totalTaxesAmount' => $price->getTaxesSum()->getAmount('VALCUR')
                    ];

                    $totalPrice = $totalPrice->add($price->getBaseFare()->add($price->getTaxesSum()));
                }

                $resultJSON['data'][] = [
                    'priceId' => $priceId,
                    'attributes' => [
                        'totalAmount' => $totalPrice->getAmount('VALCUR'),
                        'priceDetails' => $passengerPrices
                    ]
                ];
            }
        }

        return $resultJSON;
    }

}