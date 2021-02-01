<?php

namespace ReservationKit\src\Modules\Galileo\Model\Response;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price as GalileoPrice;
use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\BookingInfo as GalileoBookingInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;
use ReservationKit\src\Modules\Galileo\Model\Entity\BaggageAllowanceInfo;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;
use ReservationKit\src\Modules\Avia\Model\Entity\FareInfoRule;

class AirPriceRsp extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->AirPriceRsp)) {
            $body = $this->getResponse()->Body->AirPriceRsp;
        } else {
            throw new \Exception('Bad AirPriceRsp response content');
        }

        $result = array();

        // Прайс
        foreach ($body->AirPriceResult->AirPricingSolution as $AirPricingSolution) {
            // Прайсы для типов пассажиров
            $pricingSolution = array();

            $HostTokenList = array();
            foreach ($AirPricingSolution->HostToken as $HostToken) {
                $HostTokenList[(string) $HostToken['Key']] = (string) $HostToken;
            }

            foreach ($AirPricingSolution->AirPricingInfo as $AirPricingInfo) {
                $farePrice = new GalileoPrice();

                // HostToken
                $farePrice->setHostTokens($HostTokenList);

                // Key
                $farePrice->setKey((string) $AirPricingInfo['Key']);

                // Тип пассажира
                $farePrice->setType(str_replace('CNN', 'CHD', (string) $AirPricingInfo->PassengerType['Code']));

                // Количество пассажиров данного типа
                $farePrice->setQuantity(count($AirPricingInfo->PassengerType));

                // Стоимость
                $TotalPrice = (string) $AirPricingInfo['TotalPrice'];
                $BasePrice  = (string) $AirPricingInfo['BasePrice'];
                $EquivPrice = isset($AirPricingInfo['EquivalentBasePrice']) ? (string) $AirPricingInfo['EquivalentBasePrice'] : null;

                $farePrice->setTotalFare(MoneyHelper::parseMoneyString($TotalPrice));
                $farePrice->setBaseFare(MoneyHelper::parseMoneyString($BasePrice));
                if ($EquivPrice) {
                    $farePrice->setEquivFare(MoneyHelper::parseMoneyString($EquivPrice));
                }

                $farePrice->setApproximateTotalPrice(MoneyHelper::parseMoneyString((string) $AirPricingInfo['ApproximateTotalPrice']));
                $farePrice->setApproximateBasePrice(MoneyHelper::parseMoneyString((string) $AirPricingInfo['ApproximateBasePrice']));
                $farePrice->setApproximateTaxes(MoneyHelper::parseMoneyString((string) $AirPricingInfo['ApproximateTaxes']));

                //$farePrice->setTaxes(MoneyHelper::parseMoneyString((string) $AirPricingInfo['ApproximateTaxes']));
                $farePrice->setPricingMethod((string) $AirPricingInfo['PricingMethod']);
                $farePrice->setIncludesVAT((string) $AirPricingInfo['IncludesVAT']);

                // Возвратность (данный параметр установлен не всегда)
                if (isset($AirPricingInfo['Refundable'])) {
                    $refundable = (string) $AirPricingInfo['Refundable'];
                    $farePrice->setRefundable($refundable === 'true');
                } else {
                    $farePrice->setRefundable(false);
                }

                // Информация о тарифе TODO вынести/совместить в класс FareInfo
                $numSegment = 0;
                foreach ($AirPricingInfo->FareInfo as $FareInfoItem) {
                    $fareInfo = new GalileoFareInfo();

                    $fareInfo->setKey((string) $FareInfoItem['Key']);
                    $fareInfo->setPassengerTypeCode((string) $FareInfoItem['PassengerTypeCode']);
                    $fareInfo->setDepartureAirportCode((string) $FareInfoItem['Origin']);
                    $fareInfo->setArrivalAirportCode((string) $FareInfoItem['Destination']);
                    $fareInfo->setFareBasis((string) $FareInfoItem['FareBasis']);
                    $fareInfo->setEffectiveDate(new \RK_Core_Date((string) $FareInfoItem['EffectiveDate'], \RK_Core_Date::DATE_FORMAT_ISO_8601));

                    $fareInfo->setDepartureDate(new \RK_Core_Date((string) $FareInfoItem['DepartureDate'], \RK_Core_Date::DATE_FORMAT_DB_DATE));
                    $fareInfo->setAmount(MoneyHelper::parseMoneyString((string) $FareInfoItem['Amount']));
                    if ($FareInfoItem['NotValidBefore']) {
                        $fareInfo->setNotValidBefore(new \RK_Core_Date((string) $FareInfoItem['NotValidBefore'], \RK_Core_Date::DATE_FORMAT_DB_DATE));
                    }
                    if ($FareInfoItem['NotValidAfter']) {
                        $fareInfo->setNotValidAfter(new \RK_Core_Date((string) $FareInfoItem['NotValidAfter'], \RK_Core_Date::DATE_FORMAT_DB_DATE));
                    }
                    $fareInfo->setTaxAmount(MoneyHelper::parseMoneyString((string) $FareInfoItem['TaxAmount']));

                    // Тур-код
                    if ($FareInfoItem['TourCode']) {
                        $fareInfo->setTourCode((string) $FareInfoItem['TourCode']);
                    }

                    // Правила
                    $fareInfo->setRuleKey((string) $FareInfoItem->FareRuleKey);

                    // Брендирование
                    if (isset($FareInfoItem->Brand)) {
                        $Brand = $FareInfoItem->Brand;

                        $BrandFares = new Brand();
                        $BrandFares->setBrandID((string) $Brand['BrandID']);
                        $BrandFares->setName((string) $Brand['Name']);
                        $BrandFares->setCarrier((string) $Brand['Carrier']);

                        foreach ($Brand->Title as $title) {
                            $BrandFares->addTitle((string) $title['Type'], (string) $title);
                        }
                        foreach ($Brand->Text as $text) {
                            $BrandFares->addText((string) $text['Type'], (string) $text);
                        }
                        $fareInfo->setBrand($BrandFares);
                    }

                    $farePrice->addFareInfo($fareInfo);
                    $farePrice->addFare($numSegment, (string) $FareInfoItem['FareBasis']);

                    $numSegment++;
                }

                // Багаж
                foreach ($AirPricingInfo->BaggageAllowances->BaggageAllowanceInfo as $xmlBaggageAllowanceInfo) {
                    $baggageAllowanceInfo = new BaggageAllowanceInfo();

                    $baggageAllowanceInfo->setOrigin((string) $xmlBaggageAllowanceInfo['Origin']);
                    $baggageAllowanceInfo->setDestination((string) $xmlBaggageAllowanceInfo['Destination']);
                    $baggageAllowanceInfo->setCarrier((string) $xmlBaggageAllowanceInfo['Carrier']);

                    foreach ($xmlBaggageAllowanceInfo->TextInfo->Text as $Text) {
                        $baggageAllowanceInfo->addTextInfo((string) $Text);
                    }

                    $farePrice->addBaggageAllowances($baggageAllowanceInfo);
                }

                // Добавление связей сегментов и FareInfo
                foreach ($AirPricingInfo->BookingInfo as $BookingInfoItem) {
                    $bookingInfo = new GalileoBookingInfo();

                    $bookingInfo->setBookingCode((string) $BookingInfoItem['BookingCode']);
                    $bookingInfo->setCabinClass((string) $BookingInfoItem['CabinClass']);
                    $bookingInfo->setFareInfoRef((string) $BookingInfoItem['FareInfoRef']);
                    $bookingInfo->setSegmentRef((string) $BookingInfoItem['SegmentRef']);
                    $bookingInfo->setHostTokenRef((string) $BookingInfoItem['HostTokenRef']);

                    $farePrice->addBookingInfo($bookingInfo);
                }

                // Таксы
                foreach ($AirPricingInfo->TaxInfo as $TaxInfo) {
                    $farePrice->addTax((string) $TaxInfo['Category'], MoneyHelper::parseMoneyString((string) $TaxInfo['Amount']));
                }

                // Расчетная строка
                $farePrice->setFareCalc((string) $AirPricingInfo->FareCalc);

                // Таймлимит тарифа
                $farePrice->setTicketTimelimit(new \RK_Core_Date((string) $AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601));

                // Добавление прайсов
                $pricingSolution[$farePrice->getType()] = $farePrice;
            }

            $TotalPrice = (string) $AirPricingSolution['TotalPrice'];

            $result[$TotalPrice] = $pricingSolution;
        }

        $this->setResult($result);

        return $this;
    }
}