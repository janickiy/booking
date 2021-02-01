<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\TriPartyAgreement;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Entity\Segment as GalileoSegment;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Price as S7AgentPrice;
use ReservationKit\src\RK;

use ReservationKit\src\Modules\Avia\Model\Exception\PassengerPriceNotSetException;

class FromRkConverter
{
    /**
     * Обновляет массив $data данными из объекта бронирования $booking
     *
     * @param array $data Массив с данными заявки в формате сайта
     * @param \RK_Avia_Entity_Booking|GalileoBooking $booking Объект бронирования
     * @param bool $isHiddenDiscount Флаг наличия скрытой скидки
     * @throws PassengerPriceNotSetException
     * @throws \RK_Core_Exception
     */
    public static function updateSiteData(array & $data, \RK_Avia_Entity_Booking $booking, $isHiddenDiscount = false)
    {
        // Добавление key для выписки
        $data['type'] = 'avia_bron';

        if ($booking instanceof GalileoBooking) {
            $data['air_pricing_info'] = $booking->getAirPricingInfoRef();
        }

        // Если бронь отменена
        if ($booking->getStatus() === \RK_Avia_Entity_Booking::STATUS_CANCEL) {
            $data['status']     = 'annuled';
            $data['is_annuled'] = 'yes';

        } else {
            // PNR брони у перевозчика
            $airline_PNRS = null;
            if ($booking instanceof GalileoBooking) {
                if ($booking->getLocatorSupplier()) {
                    $airline_PNRS = $booking->getLocatorSupplier() . ' ' . $booking->getCodeSupplier();
                }

                $data['PNR_universal'] = $booking->getLocatorUniversalRecord();
                $data['PNR_aircreate'] = $booking->getLocatorAirReservation();

                $data['version_UR'] = $booking->getVersion();
                $data['restrictive_time']   = ($booking->calculateTiсketTimelimit() instanceof \RK_Core_Date) ? $booking->calculateTiсketTimelimit()->getTimestamp() : null;

            } else {
                $data['restrictive_time']   = $booking->getTimelimit()->getTimestamp();
            }

            $data['PNR']                = $booking->getLocator();
            $data['type']               = 'avia_bron';
            $data['airline_PNRS']       = array($airline_PNRS);
            $data['tariffing_datetime'] = ($booking->getBookingDate() instanceof \RK_Core_Date) ? (string) $booking->getBookingDate()->formatTo('Y-m-d H:i:s') : 0;

            // Определение установлен ли 3D договор
            $hasTourCode = $booking->hasTourCode();
            if ($booking instanceof GalileoBooking) {
                $hasTourCode = $hasTourCode || $booking->hasTourCodeInPrices();
            }
            // Отображение иконки 3D договора
            $data['isSet3DAgreement'] = $hasTourCode && !$isHiddenDiscount;

            if (empty($data['RkPrice'])) {
                $data['RkPrice'] = base64_encode(gzcompress(serialize($booking->getPrices())));
            }

            $segments_info = array();
            $segments = $booking->getSegments();

            $data['segments'] = array();
            foreach ($segments as $segmentNum => $segment) {
                $data['segments'][] = array(
                    'number'            => $segment->getFlightNumber(),
                    'airline'           => $segment->getMarketingCarrierCode(),
                    'airline_operating' => $segment->getOperationCarrierCode(),
                    'airport_out'       => $segment->getDepartureCode(),
                    'airport_in'        => $segment->getArrivalCode(),
                    'terminal_out'      => $segment->getDepartureTerminal(),
                    'terminal_in'       => $segment->getArrivalTerminal(),
                    'datetime_out'      => (string) $segment->getDepartureDate()->formatTo('Y-m-d H:i:s'),
                    'datetime_in'       => (string) $segment->getArrivalDate()->formatTo('Y-m-d H:i:s'),
                    'flight_time'       => $segment->getFlightTime(),
                    'aircraft'          => $segment->getAircraftCode(),
                    'class'             => $segment->getBaseClass(),
                    'section_number'    => $segment->getWayNumber(),
                    'segment_number'    => $segmentNum,
					'status'            => $segment->getStatus()
                );

                // Используется в GalileoUAPI
                if ($segment instanceof GalileoSegment) {
                    $data['segments'][$segmentNum]['connection'] = $segment->isNeedConnectionToNextSegment();
                }

                // segments_info для блока с пассажирами
                $segments_info[] = array(
                    'class'   => $segment->getBaseClass(),
                    'BIC'     => $segment->getSubClass(),
                    'FIC'     => $segment->getFareCode(),
                    'baggage' => $segment->getBaggage(),    // "1PC"
                    //'services' => '',
                    //'meals'    => '',
                    //'add_key'  => 'avia_bron'
                );
            }
			
			$old_passengers     = $data['passengers'];
            $data['passengers'] = array();
            
            $passengers = $booking->getPassengers();

            foreach ($passengers as $passengerNum => $passenger) {
                if ($price = $booking->getPriceByTypePassenger($passenger->getType(), false)) {

                    // Прайсы
                    $obligations = self::getObligations($price, $isHiddenDiscount);
                    if ($old_passengers[$passengerNum]['quotes'][0]['obligations']) {
                        foreach ($old_passengers[$passengerNum]['quotes'][0]['obligations'] as $key => $obligation) {
                            // Добавление и корректировка такс и сборов в обновленные данные о тарифе (если они были установлены ранее)
                            if ($obligation['type'] === 'tax') {
                                if (isset($obligation['obligations_details'])) {
                                    $codesAdded = array();

                                    foreach ($obligation['obligations_details'] as $obligationsDetails) {
                                        // 'XS', 'XQ', 'ZZ' - таксы, которые добавленны вручную
                                        if (in_array($obligationsDetails['code'], array('XS', 'XQ'/*, 'ZZ'*/))) {

                                            // Если такса не была добавлена, то добавляем
                                            // Этот if должен исправить таксы ZZ, которые нахерачились по несколько раз и продолжают плодиться хуй знает откуда
                                            if (!isset($codesAdded[$obligationsDetails['code']])) {
                                                $obligations[$key]['obligations_details'][] = $obligationsDetails;
                                                $obligations[$key]['amount'] += (float)$obligationsDetails['amount'];

                                                $codesAdded[$obligationsDetails['code']] = true;
                                            }
                                        }
                                    }
                                }
                            }

                            // Поиск fee и копирование fee в обновленные данные о тарифе
                            // WARNING: добавление сборов происходит в функции AviaAddFees, которая запускается после данного метода
                            // поэтому здесь сборы не добавляются
                            if ($obligation['type'] === 'fee') {
                                $obligations[] = $obligation;
                            }
                        }
                    }

                }

                // Возвратность
                $refundable = self::getRefundable($booking);
                if (!isset($refundable) && isset($price)) {
                    $refundable = $price->isRefundable();
                }

                // Информация о тарифе
                $quotes = array(
                    array(
                        'obligations' => $obligations,
                        'owner' => $booking->getValidatingCompany(),
                        'is_returnable' => $refundable
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
                    $quotes[0]['discount']        = '';
                    $quotes[0]['tour_code']       = ($booking->getTriPartyAgreementByCarrierCode($booking->getValidatingCompany()) instanceof TriPartyAgreement) ? $booking->getTriPartyAgreementByCarrierCode($booking->getValidatingCompany())->getTourCode() : '';

                    $discountPercent = $price->getDiscountPercent();
                    if (isset($discountPercent)) {
                        $quotes[0]['discount'] = $discountPercent;
                    }

                } else if ($isHiddenDiscount) {
                    // Если в прайсе не установлена скидка, то пытаемся восстановить ее из старых данных $data
                    // Актуально для Galileo, на всех этапах после прайсинга, т.к. Galileo не возвращает данные о скидке
                    $quotes[0]['full_tariff']     = !empty($old_passengers) ? $old_passengers[$passengerNum]['quotes'][0]['full_tariff'] : '';
                    $quotes[0]['discount_tariff'] = !empty($old_passengers) ? $old_passengers[$passengerNum]['quotes'][0]['discount_tariff'] : '';
                    $quotes[0]['extra_fee']       = !empty($old_passengers) ? $old_passengers[$passengerNum]['quotes'][0]['extra_fee'] : '';
                    $quotes[0]['discount']        = !empty($old_passengers) ? $old_passengers[$passengerNum]['quotes'][0]['discount'] : '';
                    $quotes[0]['tour_code']       = !empty($old_passengers) ? $old_passengers[$passengerNum]['quotes'][0]['tour_code'] : '';
                }

                $data['passengers'][$passengerNum] = array(
                    'lastname'       => $passenger->getLastname(),
                    'firstname'      => $passenger->getFirstname(),
                    'middlename'     => $passenger->getMiddlename(),
                    'lastname_rus'   => !empty($old_passengers) ? $old_passengers[$passengerNum]['lastname_rus'] : '',
                    'firstname_rus'  => !empty($old_passengers) ? $old_passengers[$passengerNum]['firstname_rus'] : '',
                    'middlename_rus' => !empty($old_passengers) ? $old_passengers[$passengerNum]['middlename_rus'] : '',
                    'type'     => $passenger->getType(),
                    'birthday' => $passenger->getBorndate('Y-m-d'),
                    'sex'      => $passenger->getGender(),
                    'nation'   => $passenger->getNationality(),
                    'document_type'    => !empty($old_passengers) ? $old_passengers[$passengerNum]['document_type'] : '',
                    'document_number'  => $passenger->getDocNumber(),
                    'document_country' => $passenger->getDocCountry(),
                    'phone_sms'     => !empty($old_passengers) ? $old_passengers[$passengerNum]['phone_sms'] : '',
                    'milecards'     => !empty($old_passengers) ? $old_passengers[$passengerNum]['milecards'] : '',
                    'grade'         => !empty($old_passengers) ? $old_passengers[$passengerNum]['grade'] : '',
                    'code'          => !empty($old_passengers) ? $old_passengers[$passengerNum]['code'] : '',
                    'passenger_id'  => !empty($old_passengers) ? $old_passengers[$passengerNum]['passenger_id'] : '',
                    'quotes'        => $quotes,
                    'segments_info' => $segments_info,
                );

                if ($passenger->getDocExpired()) {
                    $data['passengers'][$passengerNum]['document_date'] = (string) $passenger->getDocExpired('Y-m-d');
                }

                // Обновление номеров билетов, если они есть
                $tickets = $passenger->getTicketNumbers();
                $countSegmentNum = 0;
                if (is_array($tickets) && !empty($tickets)) {
                    // Перебор всех номеров сегментов
                    $numbersUniqueList = array();
                    foreach ($tickets as $journeyNum => $segments) {
                        foreach ($segments as $segmentNum => $ticketNum) {
                            // Удаление не нужных символов
                            $ticketNumberClean = $ticketNum;

                            $posC = strrpos($ticketNum, 'C');
                            if ($posC !== false) {
                                $ticketNumberClean = substr_replace($ticketNumberClean, '', $posC);
                            }

                            $posSlash = strrpos($ticketNumberClean, '/');
                            if ($posSlash !== false) {
                                $ticketNumberClean = substr_replace($ticketNumberClean, '', $posSlash);
                            }

                            // Формирование списка из уникальных номеров билетов у пассажира $passenger
                            $numbersUniqueList[$ticketNumberClean] = $ticketNumberClean;

                            // Запись номеров билетов в сегментс_инфо
                            $ticketNumberClean = $ticketNum;
                            $posSlash = strrpos($ticketNum, '/');
                            if ($posSlash !== false) {
                                $ticketNumberClean = substr_replace($ticketNum, '', $posSlash);
                            }

                            $data['passengers'][$passengerNum]['segments_info'][$countSegmentNum]['ticket_number'] = $ticketNumberClean;

                            $countSegmentNum++;
                        }
                    }

                    // Формирование строки из уникальных номеров билетов у пассажира $passenger. Номера перечисляются через ','
                    $numbersUniqueString = implode(',', $numbersUniqueList);

                    // Если у брони есть тарифы
                    if ($data['passengers'][$passengerNum]['quotes'][0]['obligations']) {
                        // Добавление списка номеров билетов в $obligations у пассажира $passenger
                        foreach ($data['passengers'][$passengerNum]['quotes'][0]['obligations'] as $key => $obligation) {
                            $data['passengers'][$passengerNum]['quotes'][0]['obligations'][$key]['number'] = $numbersUniqueString;
                        }
                    }

                    $data['status'] = 'ticketed';
                }
            }
        }
    }

    public static function getObligations(\RK_Avia_Entity_Price $price, $isHiddenDiscount = false)
    {
        $appCurrency = RK::getContainer()->getAppCurrency();

        // Тариф
        if ($price->getEquivFare()) {
            if ($appCurrency !== $price->getEquivFare()->getCurrency()) {
                $rate = $price->getCurrencyRates()->getRate($price->getEquivFare()->getCurrency() . '/' . $appCurrency);
                $conversionFare = $price->getEquivFare()->convert(RK::getContainer()->getAppCurrency(), $rate);
                $conversionFare = $conversionFare->roundEvenUp(5);  // Округление в большую сторону до ближайшего целого кратного 5-ти числа
            } else {
                $conversionFare = $price->getEquivFare();
            }

            $obligations = array();
            $obligations[0]['type'] = 'tariff';
            $obligations[0]['amount'] = number_format($conversionFare->getValue(), 2, '.', '');
            // Скрытая скидка
            if ($isHiddenDiscount && $price->getDiscountAmount()) {
                $obligations[0]['amount'] = number_format($conversionFare->add($price->getDiscountAmount())->getValue(), 2, '.', '');
            }
            $obligations[0]['currency'] = $conversionFare->getCurrency();

        } else if ($price->getBaseFare()) {
            if ($appCurrency !== $price->getBaseFare()->getCurrency()) {
                $rate = $price->getCurrencyRates()->getRate($price->getBaseFare()->getCurrency() . '/' . $appCurrency);
                $conversionFare = $price->getBaseFare()->convert(RK::getContainer()->getAppCurrency(), $rate);
                $conversionFare = $conversionFare->roundEvenUp(5);  // Округление в большую сторону до ближайшего целого кратного 5-ти числа
            } else {
                $conversionFare = $price->getBaseFare();
            }

            $obligations = array();
            $obligations[0]['type'] = 'tariff';
            $obligations[0]['amount'] = number_format($conversionFare->getValue(), 2, '.', '');
            // Скрытая скидка
            if ($isHiddenDiscount && $price->getDiscountAmount()) {
                $obligations[0]['amount'] = number_format($conversionFare->add($price->getDiscountAmount())->getValue(), 2, '.', '');
            }
            $obligations[0]['currency'] = $conversionFare->getCurrency();
        }

        // Таксы
        $taxesSum = new \RK_Core_Money();
        $obligations[1]['obligations_details'] = array();
        foreach ($price->getTaxes() as $num => $tax) {
            if ($appCurrency !== $tax->getAmount()->getCurrency()) {
                $rate = $price->getCurrencyRates()->getRate($tax->getAmount()->getCurrency() . '/' . $appCurrency);
                $conversionAmount = $tax->getAmount()->convert(RK::getContainer()->getAppCurrency(), $rate);
                $conversionAmount = $conversionAmount->roundEvenUp();   // Округление в большую сторону до ближайшего целого
            } else {
                $conversionAmount = $tax->getAmount();
            }

            $taxInfo = array(
                'amount'   => $conversionAmount->getValue(),
                'currency' => $conversionAmount->getCurrency(),
                'code'     => $tax->getCode()
            );

            // Детализация такс
            $obligations[1]['obligations_details'][] = $taxInfo;
            
            // Суммирование такс. Производится в валюте $appCurrency, поэтому метод $price->getTaxesSum() НЕ ПОДХОДИТ
            $taxesSum = $taxesSum->add($conversionAmount);
        }

        // Сумма такс
        $obligations[1]['type'] = 'tax';
        $obligations[1]['amount'] = number_format($taxesSum->getValue(), 2, '.', '');
        $obligations[1]['currency'] = $taxesSum->getCurrency();

        return $obligations;
    }

    public static function getRefundable(\RK_Avia_Entity_Booking $booking)
    {
        $segments = $booking->getSegments();

        $prices = array_values($booking->getPrices());

        // Если не установленый прайсы
        if (empty($prices[0])) {
            return null;
        }

        $minRefundable = null;

        foreach ($segments as $numSegment => $segment) {
            if (!isset($minRefundable)) {
                $minRefundable = 2;
            };

            // Берутся коды тарифов только из первого прайса [0],
            // т.к. коды тарифов для разных типов пассажиров одинаковые
            $fareCode        = $prices[0]->getFare($numSegment);
            $aviaCompanyCode = $booking->getValidatingCompany();

            $refundable = FareFamilies::getRefundable($aviaCompanyCode, $fareCode);

            if ($refundable < $minRefundable) {
                $minRefundable = $refundable;
            }
        }

        return $minRefundable;
    }
}