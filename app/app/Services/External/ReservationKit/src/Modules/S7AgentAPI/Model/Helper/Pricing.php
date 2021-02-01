<?php

// TODO переделать S7AgentAPI на S7Agent
namespace ReservationKit\src\Modules\S7AgentAPI\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement;
use ReservationKit\src\Modules\Avia\Model\Helper\FromRkConverter;
use ReservationKit\src\Modules\Core\Model\Money\MoneyConverter;
use ReservationKit\src\Modules\Galileo\Model\Entity\BaggageAllowanceInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo  as GalileoFareInfo;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\BaggageAllowance;
use \ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Price as S7AgentPrice;

use ReservationKit\src\RK;

class Pricing
{
    /**
     * Возвращает PriceSolution и его итоговую стоимость из массива предложений по подклассу у типов пассажиров
     *
     * @param $data
     * @param $prices
     * @return array
     */
    public static function getPriceSolutionBySubClass($data, $prices)
    {
        // Перебор прайсов
        foreach ($prices as $priceCost => $priceSolution) {

            // Перебор пассажиров
            foreach ($data['passengers'] as $passenger) {
                $passengerType = $passenger['type'];
                $passengerBICS = $passenger['BICS'];

                // Проверка существования информации о тарифе для типа пассажира
                if (!isset($priceSolution[$passengerType])) {
                    // Следующий $priceSolution
                    break;
                }

                /* @var GalileoPrice $price */
                $price = $priceSolution[$passengerType];

                $bookingInfoList = $price->getBookingInfoList();

                // Перебор информации о сегментах
                foreach ($bookingInfoList as $numSegment => $bookingInfo) {
                    // Сравнение подклассов
                    if ($bookingInfo->getBookingCode() !== $passengerBICS[$numSegment]) {
                        // Следующий $priceSolution
                        continue 3;
                    }
                }
            }

            return array($priceCost, $priceSolution);
        }

        return array(null, null);
    }

    public static function getPriceSolutionByFareBasis($data, $prices)
    {
        // Перебор прайсов
        foreach ($prices as $priceCost => $priceSolution) {

            // Перебор пассажиров
            foreach ($data['passengers'] as $passenger) {
                $passengerType = $passenger['type'];
                $passengerSegmentsInfo = $passenger['segments_info'];

                // Проверка существования информации о тарифе для типа пассажира
                if (!isset($priceSolution[$passengerType])) {
                    // Следующий $priceSolution
                    break;
                }

                /* @var GalileoPrice $price */
                $price = $priceSolution[$passengerType];

                $fareCodesList = $price->getFares();

                // Перебор информации о сегментах
                foreach ($fareCodesList as $numSegment => $fareCode) {
                    $FIC = strtok($passengerSegmentsInfo[$numSegment]['FIC'], '/');

                    // Сравнение подклассов
                    if ($fareCode !== $FIC) {
                        // Следующий $priceSolution
                        continue 3;
                    }
                }
            }

            // Коды тарифа у пассажиров соответствуют кодам в $priceSolution, следовательно возвращается это предложение
            return array($priceCost, $priceSolution);
        }

        return array(null, null);
    }

    /**
     * Преобразует результаты прайсинга в формат массива, используемого сайтом
     *
     * @param array $prices
     * @param array $data
     * @param bool $isSet3DAgreement
     * @param bool $isHiddenDiscount
     * @param null|\RK_Avia_Entity_Search_Request $bookingRK
     * @return array
     * @throws \RK_Core_Exception
     */
    public static function pricesToSiteFormat(array $prices, array $data, $isSet3DAgreement = false, $isHiddenDiscount = false, $bookingRK = null)
    {
        $results = array();
        $numPriceSolution = 1;

        $baggageAssoc = array();

        // Переопределение формата даты и времени
        foreach ($data['segments'] as $numSegment => $segment) {
            $dateTimeDep = $data['segments'][$numSegment]['datetime_out'];
            $dateTimeArr = $data['segments'][$numSegment]['datetime_in'];

            $dateTimeDepNew = \DateTime::createFromFormat('YmdHis', $dateTimeDep);
            $dateTimeArrNew = \DateTime::createFromFormat('YmdHis', $dateTimeArr);

            if (!$dateTimeDepNew instanceof \DateTime) {
                $dateTimeDepNew = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeDep);
            }

            if (!$dateTimeArrNew instanceof \DateTime) {
                $dateTimeArrNew = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeArr);
            }

            $data['segments'][$numSegment]['datetime_out'] = (string) $dateTimeDepNew->format(\RK_Core_Date::DATE_FORMAT_DB);
            $data['segments'][$numSegment]['datetime_in'] = (string) $dateTimeArrNew->format(\RK_Core_Date::DATE_FORMAT_DB);

            // Парсинг данных о багаже
            if (!empty($segment['baggage'])) {
                $typePassengerBaggage = explode(',', $segment['baggage']);
                foreach ($typePassengerBaggage as $typePassengerBaggageItem) {
                    list($typePassenger, $baggageKey, $baggageValue) = explode(':', $typePassengerBaggageItem);

                    $baggageAllowance = new BaggageAllowance();
                    $baggageAllowance->setKey($baggageKey);
                    $baggageAllowance->setBaggageValue($baggageValue);

                    $baggageAssoc[$typePassenger][$numSegment] = $baggageAllowance;
                }
            }
        }

        foreach ($prices as $priceSolution) {
            // Пассажиры
            $passengers = array();
            foreach ($data['passengers'] as $numPassenger => $passenger) {
                $type = $passenger['type'];

                $passengers[$numPassenger] = array(
                    'type' => $type
                );

                /* @var S7AgentPrice $price */
                $price = $priceSolution[$type];

                //
                if (isset($baggageAssoc[$price->getType()])) {
                    $price->setBaggageAllowance($baggageAssoc[$price->getType()]);
                }

                $segments_info = array();
                foreach ($data['segments'] as $numSegment => $segment) {
                    //$data['segments'][$numSegment]['key']         = createBase64UUID();
                    //$data['segments'][$numSegment]['keyFareInfo'] = createBase64UUID();

                    //$fareInfoRef = $price->getBookingInfoBySegmentNum($numSegment)->getFareInfoRef();

                    $segments_info[] = array(
                        'class'    => $segment['class'],    // Y
                        'BIC'      => substr($price->getFare($numSegment), 0, 1),      // Подкласс
                        'FIC'      => $price->getFare($numSegment),
                        'baggage'  => ($price->getBaggageAllowanceBySegment($numSegment) instanceof BaggageAllowance) ? $price->getBaggageAllowanceBySegment($numSegment)->getBaggageValue() : '',   // 20K Данные о багаже на любом сегменте могут отсутствовать в ответе GDS
                        //'meals'    => '',   // N
                        //'services' => ''    // 9
                    );

                    if (substr($price->getFare($numSegment), 1, 2) === 'BS') {
                        $brandName = 'basic';
                    } else {
                        $brandName = 'flex';
                    }
                }

                // Добавление segments_info
                $passengers[$numPassenger]['segments_info'] = $segments_info;

                // Прайсы
                $obligations = FromRkConverter::getObligations($price, $isHiddenDiscount);

                $quotes = array(
                    array(
                        'obligations'   => $obligations,
                        'owner'         => $data['segments'][0]['airline'],
                        'is_returnable' => false//$price->isRefundable()
                    )
                );

                // Скрытая скидка
                if (/*$isHiddenDiscount && */$price instanceof S7AgentPrice && $price->getDiscountAmount()) {
                    if ($price->getEquivFare()) {
                        $fareValue = $price->getEquivFare();
                    } else {
                        $fareValue = $price->getBaseFare();
                    }

                    $quotes[0]['full_tariff']     = $fareValue->add($price->getDiscountAmount())->getValue();
                    $quotes[0]['discount_tariff'] = $fareValue->getValue();
                    $quotes[0]['extra_fee']       = $price->getDiscountAmount()->getValue();
                    $quotes[0]['discount']        = $price->getDiscountPercent();
                    $quotes[0]['tour_code']       = ($bookingRK->getTriPartyAgreementByNum(0) instanceof TriPartyAgreement) ? $bookingRK->getTriPartyAgreementByNum(0)->getTourCode() : '';
                }

                // Добавление quotes
                $passengers[$numPassenger]['quotes'] = $quotes;

                // Название бренда берется из ADT пассажира, т.к для всех типов должно быть одинаково
                /*
                if (empty($brandName)) {
                    $fareInfo = $price->getFareInfo();
                    $fareInfo = current($fareInfo);
                    /* @var GalileoFareInfo $fareInfo */
                /*    $brandName = $fareInfo->getBrand() ? $fareInfo->getBrand()->getName() : 'NO BRAND NAME';
                }
                */
            }

            // Приведение данных к формату сайта
            $result = array(
                'brand1'     => $numPriceSolution . '.' . $brandName,
                'passengers' => $passengers,
                'segments'   => $data['segments'],
                'isSet3DAgreement' => $isSet3DAgreement,
                'system'     => SYSTEM_NAME_S7AGENT,
                'brand'      => $brandName,
                'RkPrice'    => base64_encode(gzcompress(serialize($priceSolution))),
            );

            // Добавление сбора
            AviaAddFees($result);

            $results[] = $result;

            // Если нет описания бренд тарифов, то не выводить их
            if (empty($brandName)) {
                return $results;
            }

            $brandName = null;

            $numPriceSolution++;
        }

        return $results;
    }
}