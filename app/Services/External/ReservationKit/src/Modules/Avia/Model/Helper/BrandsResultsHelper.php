<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

class BrandsResultsHelper
{
    /**
     * Возможные варианты значений поля refundable и baggage:
     * - null: параметр не применим к данной тарифной опции
     * - notAvailable: услуга не доступна
     * - free: услуга доступна, является бесплатной
     * - charge: услуга доступна за дополнительную плату
     *
     * @param \RK_Avia_Entity_Search_Request $request
     * @param \RK_Avia_Entity_Booking $offer
     * @param $response
     * @return array
     * @throws \RK_Core_Exception
     */
    public static function brandsResponseToJSON($request, $offer, $response)
    {
        $resultJSON = [];
        $pricesData = [];

        /** @var \RK_Avia_Entity_Price[] $priceSolution */
        foreach ($response as $priceSolutionNum => $priceSolution) {
            $classOfService = [];
            $cabinClass     = [];
            $typeClass      = [];
            $fareBasis      = [];
            $fareName       = [];
            $refundable     = [];
            $baggage        = [];
            $baggageMeasure = [];
            $carryOn        = [];

            $totalPrice = new \RK_Core_Money();

            // Объединение данных о сегментах в массив для отображения в прайсах
            foreach ($offer->getSegments() as $segmentNum => $segment) {
                $cabinClass[]     = $segment->getBaseClass();
                $typeClass[]      = $segment->getTypeClass() ? $segment->getTypeClass() : FareFamilies::getInfo('baseClass', $segment->getOperationCarrierCode(), $segment->getFareCode());
                $baggageMeasure[] = $segment->getBaggageMeasure();
                $seats[] = $segment->getAllowedSeatsBySubclass($segment->getSubClass());
            }

            // Объединение данных пассажиров
            foreach ($priceSolution as $typePassenger => $price) {
                // Итоговая стоимость предложения
                $totalPrice = $totalPrice->add($price->getTotalFare());

                // TODO берется из первого пассажира, т.к. код тарифа должен быть одинаков для всех пассажиров
                if (empty($fareBasis)) {
                    $fareBasis = $price->getFares();

                    // Описание тарифа по коду тарифа
                    if (is_array($fareBasis)) {
                        foreach ($fareBasis as $segmentNum => $fareCode) {
                            $classOfService[] = substr($fareCode, 0, 1);

                            $segment = $offer->getSegment($segmentNum);

                            // TODO перенести заполнение FareFamilies на этап парсинга ответа от системы
                            $fareName[]   = FareFamilies::getInfo('description', $segment->getOperationCarrierCode(), $fareCode);
                            $refundable[] = FareFamilies::getInfo('refundable', $segment->getOperationCarrierCode(), $fareCode) === 'free' ? true : false;
                            $baggage[]    = FareFamilies::getInfo('baggage', $segment->getOperationCarrierCode(), $fareCode) === 'free' ? true : false;
                            $carryOn[]    = FareFamilies::getInfo('carryOn', $segment->getOperationCarrierCode(), $fareCode) === 'free' ? true : false;
                        }
                    }
                }
            }

            // Прайсы
            $pricesData[] = [
                'offerId'        => $offer->getId(),
                'totalAmount'    => $totalPrice->getAmount('VAL'),
                'classOfService' => $classOfService,
                'cabinClass'     => $cabinClass,
                'typeClass'      => $typeClass,
                'fareBasis'      => $fareBasis,
                'fareName'       => $fareName,
                'refundable'     => $refundable,
                'baggage'        => $baggage,
                'baggageMeasure' => $baggageMeasure,
                'carryOn'        => $carryOn
            ];
        }

        $offerAttributes = [
            'prices' => $pricesData
        ];

        if ($offer->getRequisiteId()) {
            $offerAttributes['ruid'] = $offer->getRequisiteId();
        }

        $resultJSON['data'] = [
            'type' => 'brands',
            'attributes' => $offerAttributes
        ];

        return $resultJSON;
    }
}