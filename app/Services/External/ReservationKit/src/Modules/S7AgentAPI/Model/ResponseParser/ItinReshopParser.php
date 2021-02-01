<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser;

use ReservationKit\src\Modules\Core\Model\Entity\CurrencyRates;
use ReservationKit\src\Modules\Core\Model\Money\MoneyHelper;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Params\Passenger;
use ReservationKit\src\Modules\Avia\Model\Entity\Segment;
use ReservationKit\src\Modules\Avia\Model\Entity\FareInfo;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\BaggageAllowance;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Booking as S7AgentBooking;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Segment as S7AgentSegment;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Price as S7AgentPrice;
use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\FareInfo as S7AgentFareInfo;

use ReservationKit\src\Modules\Galileo\Model\Entity\FareInfo as GalileoFareInfo;
use ReservationKit\src\Modules\Galileo\Model\Entity\Brand;

use ReservationKit\src\Modules\Galileo\Model\Abstracts\Response;
use ReservationKit\src\Modules\Galileo\Model\Helper\Request;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;
use ReservationKit\src\RK;

class ItinReshopParser extends Response
{
    public function __construct($response)
    {
        $this->setResponse($response);
    }

    public function parse()
    {
        if (isset($this->getResponse()->Body->ItinReshopRS)) {
            $body = $this->getResponse()->Body->ItinReshopRS;
        } else {
            throw new S7AgentException('Bad ' . __CLASS__ . ' response content');
        }

        $result = array();

        // Список типов пассажиров для поиска и их количество
        $PassengersAssociateList = array();
        foreach ($body->Response->Passengers->Passenger as $Passenger) {
            $typePassenger = (string) $Passenger->PTC;
            $countPassenger = (string) $Passenger->PTC['Quantity'];

            if ($countPassenger > 0) {
                $PassengersAssociateList[(string) $Passenger['ObjectKey']] = new Passenger($typePassenger, $countPassenger);
            }
        }

        // Список доступности багажа
        $baggageAllowanceList = [];
        if (isset(
            $body->Response,
            $body->Response->DataList,
            $body->Response->DataList->CheckedBagAllowanceList,
            $body->Response->DataList->CheckedBagAllowanceList->CheckedBagAllowance)) {
            // Создание списка доступности багажа
            foreach ($body->Response->DataList->CheckedBagAllowanceList->CheckedBagAllowance as $CheckedBagAllowance) {
                if (!isset($baggageAllowanceList[(string) $CheckedBagAllowance['ListKey']])) {
                    $baggageAllowanceList[(string) $CheckedBagAllowance['ListKey']] = [];
                }

                $baggageAllowance = new BaggageAllowance();
                $baggageAllowance->setKey((string) $CheckedBagAllowance['ListKey']);

                if (isset($CheckedBagAllowance->PieceAllowance)) {
                    $baggageAllowance->setBaggageValue((string) $CheckedBagAllowance->PieceAllowance->Descriptions->Description->Text);
                }
                if (isset($CheckedBagAllowance->AllowanceDescription)) {
                    $baggageAllowance->setBaggageValue((string) $CheckedBagAllowance->AllowanceDescription->Descriptions->Description->Text);
                }

                $baggageAllowanceList[$baggageAllowance->getKey()] = $baggageAllowance;
            }
        }

        // Список тарифов
        if (isset(
            $body->Response,
            $body->Response->ReShopOffers,
            $body->Response->ReShopOffers->ReShopOffer,
            $body->Response->ReShopOffers->ReShopOffer->ReShopPricedOffer,
            $body->Response->ReShopOffers->ReShopOffer->ReShopPricedOffer->OfferPrice)) {

            // Валюта приложения
            $currencyRK = RK::getContainer()->getAppCurrency();

            // Прайсы для типов пассажиров
            $pricingSolution = array();

            // Тарифы для типов пассажиров
            foreach ($body->Response->ReShopOffers->ReShopOffer->ReShopPricedOffer->OfferPrice as $OfferPrice) {
                $farePrice = new S7AgentPrice();

                // Ссылки на соответствующие блоки с данными
                foreach ($OfferPrice->RequestedDate->Associations as $Associations) {

                    // Соответствие тарифа типу пассажира
                    if (isset($Associations->AssociatedTraveler, $Associations->AssociatedTraveler->TravelerReferences)) {
                        $TravelerReferences = (string) $Associations->AssociatedTraveler->TravelerReferences;

                        /* @var $AnonymousTraveler Passenger */
                        $AnonymousTraveler = $PassengersAssociateList[$TravelerReferences];

                        // Тип пассажира
                        $farePrice->setType(str_replace('CNN', 'CHD', $AnonymousTraveler->getType()));

                        // Количество пассажиров данного типа
                        $farePrice->setQuantity($AnonymousTraveler->getCount());
                    }

                    // Багаж
                    if (isset(
                        $Associations->ApplicableFlight,
                        $Associations->ApplicableFlight->FlightSegmentReference,
                        $Associations->ApplicableFlight->FlightSegmentReference->BagDetailAssociation,
                        $Associations->ApplicableFlight->FlightSegmentReference->BagDetailAssociation->CheckedBagReferences)) {
                        // Example, "BG1 BG2"
                        $CheckedBagReferences = (string) $Associations->ApplicableFlight->FlightSegmentReference->BagDetailAssociation->CheckedBagReferences;
                        $CheckedBagReferencesArr = explode(' ', $CheckedBagReferences);

                        // Добавление данных о багаже
                        foreach ($CheckedBagReferencesArr as $CheckedBagReference) {
                            if (isset($baggageAllowanceList[$CheckedBagReference])) {
                                $farePrice->addBaggageAllowance($baggageAllowanceList[$CheckedBagReference]);
                            }
                        }
                    }

                    // Соответствие сегментов и установка их в бронь, если еще НЕ установлены
                    // ...
                }

                // Стоимость
                $FarePrice  = $OfferPrice->RequestedDate->PriceDetail;

                // Таксы
                if (isset($FarePrice->Taxes->Breakdown)) {
                    foreach ($FarePrice->Taxes->Breakdown->Tax as $Tax) {
                        $farePrice->addTax((string) $Tax->TaxCode, new \RK_Core_Money((string) $Tax->Amount, (string) $Tax->Amount['Code']));
                    }
                }

                //
                $BasePrice  = new \RK_Core_Money((string) $FarePrice->BaseAmount, (string) $FarePrice->BaseAmount['Code']);
                $EquivPrice = new \RK_Core_Money((string) $FarePrice->FareFiledIn->BaseAmount, (string) $FarePrice->FareFiledIn->BaseAmount['Code']); // TODO найти и проверить формат данных у тарифов в валюте

                $farePrice->setBaseFare($BasePrice);
                if (0 && $EquivPrice) {
                    $farePrice->setEquivFare($EquivPrice);
                }

                // Полная стоимость тарифа
                $TotalPrice = $BasePrice->add($farePrice->getTaxesSum())->mult($farePrice->getQuantity());  // Прибавление к базовому тарифу суммы такс, затем умножение на количество поссажиров данного (ADT, CHD или INF) типа
                $farePrice->setTotalFare($TotalPrice);

                // Скидка
                if (isset($FarePrice->Discount, $FarePrice->Discount->DiscountAmount)) {
                    $DiscountAmount  = new \RK_Core_Money((string) $FarePrice->Discount->DiscountAmount, (string) $FarePrice->Discount->DiscountAmount['Code']);

                    $farePrice->setDiscountAmount($DiscountAmount);
                    $farePrice->setDiscountPercent((int) $FarePrice->Discount->DiscountPercent);
                }

                // Курсы валют
                if ($FarePrice->FareFiledIn->ExchangeRate) {
                    // TODO меотд setExchangeRate сделать базовым для сущности Price для всех GDS
                    $farePrice->setExchangeRate((string) $FarePrice->FareFiledIn->ExchangeRate);

                    $rateFrom = (string) $FarePrice->BaseAmount['Code'];
                    $rateTo   = (string) $FarePrice->FareFiledIn->BaseAmount['Code'];

                    if ($rateFrom !== $rateTo) {
                        $exchangeRate = (string) $FarePrice->FareFiledIn->ExchangeRate;
                        $exchangeRate = ((int) ($exchangeRate * 1000)) / 1000;

                        $currencyRates = new CurrencyRates();
                        $currencyRates->addRate($rateFrom . '/' . $rateTo, 1 / (float) $exchangeRate);
                        $currencyRates->addRate($rateTo . '/' . $rateFrom, (float) $exchangeRate);

                        $farePrice->setCurrencyRates($currencyRates);
                    }
                }

                // Доплаты
                if (isset($FarePrice->Surcharges, $FarePrice->Surcharges->Surcharge, $FarePrice->Surcharges->Surcharge->Total)) {
                    $farePrice->setTotalSurcharge(new \RK_Core_Money((string) $FarePrice->Surcharges->Surcharge->Total, (string) $FarePrice->Surcharges->Surcharge->Total['Code']));
                }

                //$refundable = (string) $AirPricingInfo['Refundable'];
                //$farePrice->setRefundable(($refundable === 'true') ? true : false);

                $segmentNum = 0;
                foreach ($OfferPrice->FareDetail->FareComponent as $FareComponent) {
                    // Код тарифа
                    $farePrice->addFare($segmentNum, (string) $FareComponent->FareBasis->FareBasisCode->Code);

                    // Расчетная строка FIXME для каждого сегмента своя. Метод setFareCalc этого не учитывает
                    if (empty($farePrice->getFareCalc())) {
                        $farePrice->setFareCalc((string) $OfferPrice->FareDetail->FareComponent->FareRules->Remarks->Remark);
                    }

                    //
                    if (empty($farePrice->getEndorsments())) {
                        foreach ($OfferPrice->FareDetail->FareComponent->FareRules->Ticketing->Endorsements->Endorsement as $Endorsement) {
                            $farePrice->addEndorsment((string) $Endorsement);
                        }
                    }

                    if (isset($FareComponent->TicketDesig)) {
                        $farePrice->addTicketDesignator((string) $FareComponent->TicketDesig);
                    }

                    $segmentNum++;
                }

                // Добавление прайсов
                $pricingSolution[$farePrice->getType()] = $farePrice;
            }

            // Валидирующая компания
            //$booking->setValidatingCompany((string) $AirlineOffer->OfferID['Owner']);

            // Таймлимит
            //$booking->setTimelimit(new \RK_Core_Date((string) $AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_SERVICES));

            $TotalPrice = (string) $body->Response->ReShopOffers->ReShopOffer->TotalPrice->DetailCurrencyPrice->Total;

            $result[$TotalPrice] = $pricingSolution;
        }

        $this->setResult($result);

        return $result;

        /* --- */

        // Брендирование
        if (isset($body->BrandList)) {
            $BrandList = array();

            foreach ($body->BrandList->Brand as $Brand) {
                $BrandFares = new Brand();
                $BrandFares->setBrandID((string) $Brand['BrandID']);
                $BrandFares->setName((string) $Brand['Name']);
                $BrandFares->setCarrier((string) $Brand['Carrier']);
                //$BrandFares->setBrandedDetailsAvailable((string) $Brand['BrandedDetailsAvailable']);

                // Список заголовков
                foreach ($Brand->Title as $title) {
                    $BrandFares->addTitle((string) $title['Type'], (string) $title);
                }
                // Список текстов
                foreach ($Brand->Text as $text) {
                    $BrandFares->addText((string) $text['Type'], (string) $text);
                }

                $BrandList[(string) $Brand['BrandID']] = $BrandFares;
            }
        }

        // Информация о тарифе
        $FareInfoList = array();
        foreach ($body->FareInfoList->FareInfo as $AirFareInfo) {
            $FareInfo = new GalileoFareInfo();

            // Код тарифа
            $FareInfo->setFareCode((string) $AirFareInfo['FareBasis']);

            // Багаж (по количеству)
            if (isset($AirFareInfo->BaggageAllowance->NumberOfPieces)) {
                $NumberOfPieces = (string) $AirFareInfo->BaggageAllowance->NumberOfPieces;
                $FareInfo->setBaggageAllowance($NumberOfPieces . 'PC');
            }

            // Багаж (по весу)
            if (isset($AirFareInfo->BaggageAllowance->MaxWeight,
                $AirFareInfo->BaggageAllowance->MaxWeight['Value'],
                $AirFareInfo->BaggageAllowance->MaxWeight['Unit'])) {
                $Value = (string) $AirFareInfo->BaggageAllowance->MaxWeight['Value'];
                $Unit  = (string) $AirFareInfo->BaggageAllowance->MaxWeight['Unit'];

                $FareInfo->setBaggageAllowance($Value . str_replace(array('Kilograms'), array('K'), $Unit));
            }

            // Тур-код
            if (isset($AirFareInfo['TourCode'])) {
                $FareInfo->setTourCode((string) $AirFareInfo['TourCode']);
            }

            // Ключи для правил
            $FareInfo->setRuleKey((string) $AirFareInfo->FareRuleKey);

            // Указатель скидки
            $FareInfo->setFareTicketDesignator((string) $AirFareInfo->FareTicketDesignator['Value']);

            // Информация о бренде
            if (isset($AirFareInfo->Brand, $AirFareInfo->Brand['BrandID'], $BrandList)) {
                $BrandID = (string) $AirFareInfo->Brand['BrandID'];
                $FareInfo->setBrand($BrandList[$BrandID]);
            }

            $FareInfoList[(string) $AirFareInfo['Key']] = $FareInfo;
        }

        // Сегменты
        $i = 0;
        $AirSegmentList = array();
        foreach ($body->AirSegmentList->AirSegment as $AirSegment) {
            $segment = new GalileoSegment();

            $segment->setKey((string) $AirSegment['Key']);

            $segment->setWayNumber((string) $AirSegment['Group']);

            // Отправление
            $segment->setDepartureCode((string) $AirSegment['Origin']);
            $segment->setDepartureDate(new \RK_Core_Date((string) $AirSegment['DepartureTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601));
            $segment->setDepartureTerminal((string) $body->FlightDetailsList->FlightDetails[$i]['OriginTerminal']);

            // Прибытие
            $segment->setArrivalCode((string) $AirSegment['Destination']);
            $segment->setArrivalDate(new \RK_Core_Date((string) $AirSegment['ArrivalTime'], \RK_Core_Date::DATE_FORMAT_ISO_8601));
            $segment->setArrivalTerminal((string) $body->FlightDetailsList->FlightDetails[$i]['DestinationTerminal']);

            // Оперирующая компания
            $operatingCarrier = isset($AirSegment->CodeshareInfo) ? (string) $AirSegment->CodeshareInfo['OperatingCarrier'] : (string) $AirSegment['Carrier'];
            $segment->setOperationCarrierCode($operatingCarrier);

            // Маркетинговая компания
            $segment->setMarketingCarrierCode((string) $AirSegment['Carrier']);

            // Номер рейса
            $segment->setFlightNumber((string) $AirSegment['FlightNumber']);

            // Время перелета
            $segment->setFlightTime((string) $AirSegment['FlightTime']);

            // Дальность перелета
            $segment->setFlightDistance((string) $AirSegment['Distance']);

            // Номер борта
            $segment->setAircraftCode((string) $AirSegment['Equipment']);

            //$segment->setOptionalServicesIndicator((string) $AirSegment['OptionalServicesIndicator']);
            //$segment->setChangeOfPlane((string) $AirSegment['ChangeOfPlane']);
            //$segment->setTravelTime((string) $body->FlightDetailsList->FlightDetails[$i]['TravelTime']);

            $AirSegmentList[(string) $AirSegment['Key']] = $segment;

            // Счетчик сегментов
            $i++;
        }

        // Прайсы
        $AirPricingSolutionList = array();
        foreach ($body->AirPricingSolution as $AirPricingSolution) {
            $booking = new \RK_Avia_Entity_Booking();

            // Прайсы для типов пассажиров
            foreach ($AirPricingSolution->AirPricingInfo as $AirPricingInfo) {
                $farePrice = new GalileoPrice();

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

                // Таксы
                foreach ($AirPricingInfo->TaxInfo as $TaxInfo) {
                    $farePrice->addTax((string) $TaxInfo['Category'], MoneyHelper::parseMoneyString((string) $TaxInfo['Amount']));
                }

                $refundable = (string) $AirPricingInfo['Refundable'];
                $farePrice->setRefundable(($refundable === 'true') ? true : false);

                // Расчетная строка
                $farePrice->setFareCalc((string) $AirPricingInfo->FareCalc);

                // Добавление прайсов
                $booking->addPrice($farePrice->getType(), $farePrice);

                // Валидирующая компания
                $booking->setValidatingCompany((string) $AirPricingInfo['PlatingCarrier']);

                // Таймлимит
                $booking->setTimelimit(new \RK_Core_Date((string) $AirPricingInfo['LatestTicketingTime'], \RK_Core_Date::DATE_FORMAT_SERVICES));
            }

            if (isset($AirPricingSolution->Connection)) {
                $connections = array();
                foreach ($AirPricingSolution->Connection as $Connection) {
                    $connections[(string) $Connection['SegmentIndex']] = (string) $Connection['SegmentIndex'];
                }
            }

            // Количество мест в сегменте (берется только для первого пассажира)
            foreach ($AirPricingSolution->AirPricingInfo[0]->BookingInfo as $BookingInfo) {
                $SegmentRef = (string) $BookingInfo['SegmentRef'];
                $FareInfoRef = (string) $BookingInfo['FareInfoRef'];

                /* @var Segment $segment */
                $segment = $AirSegmentList[$SegmentRef];
                $segment->setKey($SegmentRef);
                $segment->setFareInfoRef($FareInfoRef);

                $segment->addAllowedSeat((string) $BookingInfo['BookingCode'], (string) $BookingInfo['BookingCount']);

                /* @var GalileoFareInfo $fareInfo */
                $fareInfo = $FareInfoList[$FareInfoRef];
                $segment->setBaggage($fareInfo->getBaggageAllowance());

                // Код тарифа
                $segment->setFareCode($fareInfo->getFareCode());    //$segment->setBaseFare($fareInfo->getFareCode());

                // Тип класса сегмента
                $segment->setTypeClass((string) $BookingInfo['CabinClass']);
                // Базовый класс сегмента
                $segment->setBaseClass(Request::getBaseClassByType($segment->getTypeClass()));
                // Подкласс сегмента
                $segment->setSubClass((string) $BookingInfo['BookingCode']);
            }

            // Добавление сегментов
            $numJourney = 0;
            foreach ($AirPricingSolution->Journey as $Journey) {
                $numSegment = 0;
                foreach ($Journey->AirSegmentRef as $AirSegmentRef) {
                    $SegmentRef = (string) $AirSegmentRef['Key'];

                    /* @var Segment $segment */
                    $segment = $AirSegmentList[$SegmentRef];
                    $segment->setWayNumber($numJourney);

                    $booking->addSegment(clone $segment);

                    // Если есть соединение для сегмента, то устанавливаем его
                    if (isset($connections[$numSegment])) {
                        $booking->getLastSegment()->setNeedConnectionToNextSegment(true);
                    }

                    $numSegment++;
                }
                $numJourney++;
            }

            // Добавление реквизитов
            $booking->setRequisiteRules(Requisites::getInstance()->getRules());

            $results[] = $booking;
        }

        $this->setResult($results);

        return $results;
    }
}