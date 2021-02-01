<?php

namespace ReservationKit\src\Modules\Galileo\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Helper\FromRkConverter;
use ReservationKit\src\Modules\Core\Model\Money\MoneyConverter;
use ReservationKit\src\Modules\Galileo\Model\Entity\BaggageAllowanceInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Entity\BookingInfo as GalileoBookingInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo  as GalileoFareInfo;
use \ReservationKit\src\Modules\Galileo\Model\Entity\Price as GalileoPrice;
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

    /**
     * В объекте прайсинга коды тарифов присваиваются плечам.
     * В бронях коды тарифов присваиваются сегментам.
     *
     * Внимание: из-за несоответсвия ключей у кодов тарифов могут возникать ошибки. Необходимо учитывать эту специфику.
     *
     * @param $data
     * @param $prices
     * @return array
     */
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

                // Перебор сегментов и сравнивание кода тарифа у сегмента с кодом тарифа из прайсинга
                foreach ($data['segments'] as $numSegment => $segment) {
                    // Код тарифа из массива данных заявки
                    $FIC = strtok($passengerSegmentsInfo[$numSegment]['FIC'], '/');

                    // Код тарифа из объекта прайсинга
                    // Внимание: код тарифа после прайсинга присваивается плечу, а не сегменту. Поэтому используется номер плеча
                    $fareCode = $price->getFares()[$segment['section_number']];

                    // Сравнение кодов тарифов
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
     * @return array
     */
    public static function pricesToSiteFormat(array $prices, array $data, $isSet3DAgreement = false, $isHiddenDiscount = false)
    {
        $results = array();
        $numPriceSolution = 1;

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
        }

        foreach ($prices as $priceSolution) {
            // Пассажиры
            $passengers = array();
            foreach ($data['passengers'] as $numPassenger => $passenger) {
                $type = $passenger['type'];

                $passengers[$numPassenger] = array(
                    'type' => $type
                );

                /* @var GalileoPrice $price */
                $price = $priceSolution[$type];

                $segments_info = array();
                foreach ($data['segments'] as $numSegment => $segment) {
                    //$data['segments'][$numSegment]['key']         = createBase64UUID();
                    //$data['segments'][$numSegment]['keyFareInfo'] = createBase64UUID();

                    $fareInfoRef = $price->getBookingInfoBySegmentNum($numSegment)->getFareInfoRef();

                    $segments_info[] = array(
                        'class'    => $segment['class'],    // Y
                        'BIC'      => $price->getBookingInfoBySegmentNum($numSegment)->getBookingCode(),    // Подкласс
                        'FIC'      => $price->getFareInfoByRef($fareInfoRef)->getFareBasis(),
                        'baggage'  => ($price->getBaggageAllowancesByNumSegment($numSegment) instanceof BaggageAllowanceInfo) ? $price->getBaggageAllowancesByNumSegment($numSegment)->getTextInfoByNum(0) : '',   // 20K Данные о багаже на любом сегменте могут отсутствовать в ответе GDS
                        //'meals'    => '',   // N
                        //'services' => ''    // 9
                    );
                }

                // Добавление segments_info
                $passengers[$numPassenger]['segments_info'] = $segments_info;

                // Прайсы
                $obligations = FromRkConverter::getObligations($price, $isHiddenDiscount);

                $quotes = array(
                    array(
                        'obligations'   => $obligations,
                        'owner'         => $data['segments'][0]['airline'],
                        'is_returnable' => $price->isRefundable()
                    )
                );

                // Скрытая скидка
                if ($isHiddenDiscount && $price->getDiscountAmount()) {
                    if ($price->getEquivFare()) {
                        $fareValue = $price->getEquivFare();
                    } else {
                        $fareValue = $price->getBaseFare();
                    }

                    $quotes[0]['full_tariff']     = $fareValue->add($price->getDiscountAmount())->getValue();
                    $quotes[0]['discount_tariff'] = $fareValue->getValue();
                    $quotes[0]['extra_fee']       = $price->getDiscountAmount()->getValue();
                }

                // Добавление quotes
                $passengers[$numPassenger]['quotes'] = $quotes;

                // Название бренда берется из ADT пассажира, т.к для всех типов должно быть одинаково
                if (empty($brandName)) {
                    $fareInfo = $price->getFareInfo();
                    $fareInfo = current($fareInfo);
                    /* @var GalileoFareInfo $fareInfo */
                    $brandName = $fareInfo->getBrand() ? $fareInfo->getBrand()->getName() : 'NO BRAND NAME';
                }
            }

            // Приведение данных к формату сайта
            $result = array(
                'brand1'     => $numPriceSolution . '.' . $brandName,
                'passengers' => $passengers,
                'segments'   => $data['segments'],
                'isSet3DAgreement' => $isSet3DAgreement,
                'system'     => SYSTEM_NAME_GALILEO_UAPI,
                'pcc'        => empty($data['pcc']) ? str_replace('galileoUAPI_', '', $data['system']) : $data['pcc'],
                'brand'      => $brandName,
                'RkPrice'    => base64_encode(gzcompress(serialize($priceSolution))),
                //'isBrand'    => ($numPriceSolution === 1) ? false : true    // Брендированым считаем любой тариф в списке отличный от самого первого (самого дешевого)
            );

            // Добавление сбора
            AviaAddFees($result);

            //
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

    /**
     * Рассчитывает размер скидки и устанавливает скидку в $discountedPrice
     *
     * @param array $discountedPrices Массив прайсов со скидкой
     * @param array $originPrices Массив прайсов без скидки
     * @throws \RK_Core_Exception
     */
    public static function calculateAndSetDiscount($discountedPrices, $originPrices)
    {
        // Первичная проверка соотвествия тарифов по коду тарифа
        foreach ($discountedPrices as $discountedPriceWithTypePassengers) {
            /* @var GalileoPrice[] $discountedPriceWithTypePassengers */
            foreach ($discountedPriceWithTypePassengers as $typePassengerDiscount => $discountedPrice) {
                $discountCodeFares     = $discountedPrice->getFares();
                $discountCodeFaresLine = implode(':', $discountCodeFares);

                foreach ($originPrices as $originPricesWithTypePassengers) {
                    /* @var GalileoPrice[] $originPricesWithTypePassengers */

                    if (isset($originPricesWithTypePassengers[$typePassengerDiscount]) &&
                        implode(':', $originPricesWithTypePassengers[$typePassengerDiscount]->getFares()) === $discountCodeFaresLine) {

                        try {
                            // Разница между прайсом с 3D договором и прайсом без 3D договора
                            if ($originPricesWithTypePassengers[$typePassengerDiscount]->getEquivFare()) {
                                $sub = $originPricesWithTypePassengers[$typePassengerDiscount]->getEquivFare()->sub($discountedPrice->getEquivFare());
                            } else {
                                $sub = $originPricesWithTypePassengers[$typePassengerDiscount]->getBaseFare()->sub($discountedPrice->getBaseFare());
                            }

                            $discountedPrice->setDiscountAmount($sub);
                            //$discountedPrice->setDiscountPercent();
                        } catch (\Exception $e) {
                            // Не удалось рассчитать скидку
                        }

                    }

                }
            }
        }

        // Возвращает первую буквы строки
        $getFirstLetter = function ($value) {
            return $value[0];
        };

        // Вторичная проверка соответствия тарифов по первой букве (подклассу) кода тарифа
        foreach ($discountedPrices as $discountedPriceWithTypePassengers) {
            /* @var GalileoPrice[] $discountedPriceWithTypePassengers */
            foreach ($discountedPriceWithTypePassengers as $typePassengerDiscount => $discountedPrice) {
                // Если скидка проставилась на первичной проверке, то не выполняем проверку и переходим к следующему прайсу
                if ($discountedPrice->getDiscountAmount()) {
                    continue;
                }

                $discountCodeFares      = $discountedPrice->getFares();
                $discountCodeFaresLine  = implode(':', array_map($getFirstLetter, $discountCodeFares));

                foreach ($originPrices as $originPricesWithTypePassengers) {
                    /* @var GalileoPrice[] $originPricesWithTypePassengers */

                    if (isset($originPricesWithTypePassengers[$typePassengerDiscount]) &&
                        implode(':', array_map($getFirstLetter, $originPricesWithTypePassengers[$typePassengerDiscount]->getFares())) === $discountCodeFaresLine) {

                        try {
                            // Разница между прайсом с 3D договором и прайсом без 3D договора
                            if ($originPricesWithTypePassengers[$typePassengerDiscount]->getEquivFare()) {
                                $sub = $originPricesWithTypePassengers[$typePassengerDiscount]->getEquivFare()->sub($discountedPrice->getEquivFare());
                            } else {
                                $sub = $originPricesWithTypePassengers[$typePassengerDiscount]->getBaseFare()->sub($discountedPrice->getBaseFare());
                            }

                            $discountedPrice->setDiscountAmount($sub);
                            //$discountedPrice->setDiscountPercent();
                        } catch (\Exception $e) {
                            // Не удалось рассчитать скидку
                        }

                    }

                }
            }
        }

        // Возвращает подкласс сегмента
        $getClassOfService = function ($value) {
            if ($value instanceof GalileoBookingInfo) {
                return $value->getBookingCode();
            }
            return '';
        };

        // Третичная проверка соотвествия тарифов по коду тарифа
        foreach ($discountedPrices as $discountedPriceWithTypePassengers) {
            /* @var GalileoPrice[] $discountedPriceWithTypePassengers */
            foreach ($discountedPriceWithTypePassengers as $typePassengerDiscount => $discountedPrice) {
                // Если скидка проставилась на первичной проверке, то не выполняем проверку и переходим к следующему прайсу
                if ($discountedPrice->getDiscountAmount() && $discountedPrice->getDiscountAmount()->getValue() != 0) {
                    continue;
                }

                $discountCodeFares      = $discountedPrice->getBookingInfoList();
                $discountCodeFaresLine  = implode(':', array_map($getClassOfService, $discountCodeFares));

                foreach ($originPrices as $originPricesWithTypePassengers) {
                    /* @var GalileoPrice[] $originPricesWithTypePassengers */

                    if (isset($originPricesWithTypePassengers[$typePassengerDiscount]) &&
                        implode(':', array_map($getClassOfService, $originPricesWithTypePassengers[$typePassengerDiscount]->getBookingInfoList())) === $discountCodeFaresLine) {

                        try {
                            // Разница между прайсом с 3D договором и прайсом без 3D договора
                            if ($originPricesWithTypePassengers[$typePassengerDiscount]->getEquivFare()) {
                                $sub = $originPricesWithTypePassengers[$typePassengerDiscount]->getEquivFare()->sub($discountedPrice->getEquivFare());
                            } else {
                                $sub = $originPricesWithTypePassengers[$typePassengerDiscount]->getBaseFare()->sub($discountedPrice->getBaseFare());
                            }

                            $discountedPrice->setDiscountAmount($sub);
                            //$discountedPrice->setDiscountPercent();
                        } catch (\Exception $e) {
                            // Не удалось рассчитать скидку
                        }

                    }

                }
            }
        }
    }

    /**
     * @param GalileoBooking $booking
     * @param $data
     */
    public static function restoreDiscount($booking, $data)
    {
        foreach ($booking->getPrices() as $typePassenger => $price) {
            foreach ($data['passengers'] as $sitePassenger) {

                if ($sitePassenger['type'] === $typePassenger) {
                    if (isset($sitePassenger['quotes'][0]['extra_fee'])) {
                        $price->setDiscountAmount(new \RK_Core_Money($sitePassenger['quotes'][0]['extra_fee'], RK::getContainer()->getAppCurrency()));
                    }
                }

            }
        }
    }
}