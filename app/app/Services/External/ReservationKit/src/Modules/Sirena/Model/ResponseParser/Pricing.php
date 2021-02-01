<?php

class RK_Sirena_Response_Pricing extends RK_Sirena_Response
{
    // Класс в формате сайта
    private $requestClass = "";

    function setRequestClass($class) {
        $this->requestClass = $class;
    }

    /**
     * Парсер ответа
     * @param bool $variant_mode
     * @return array
     * @throws RK_Sirena_Exception
     */
    public function parse($variant_mode = false)
    {

        global $CONFIG;

        // Наличие ответа
        if (is_null($this->_responseContent)) {
            throw new RK_Sirena_Exception('Pricing response not contains responseContent');
        }

        // Сервис ответа
        if (isset($this->_responseContent->answer)) {
            $body = $this->_responseContent->answer;
        } else {
            throw new RK_Sirena_Exception('Bad Pricing response content');
        }


        $typePassAssoc  = array('AAT'=>'ADT', 'CNN'=>'CHD', 'INF'=>'INF', 'INS'=>'INS');

        $pricing_mode = ($variant_mode)?"pricing_variant":"pricing";

        $results = array();

        // Предложения перелета
        foreach ($body->$pricing_mode->variant as $variant) {
            $booking = new RK_Avia_Entity_Booking();

            // Массив цен всех сегментов
            $segmentFarePrices = array();

            // Плечи
            $journeyNum = -1;
            $prevSegmentNum = "";

            foreach ($variant->flight as $flight) {
                $segment = new RK_Avia_Entity_Segment();

                // Инкрементируем индекс плеча, если сменился направление маршрута
                $iSegmentNum = (string)$flight["iSegmentNum"];
                if ($prevSegmentNum != $iSegmentNum) {
                    $prevSegmentNum = $iSegmentNum;
                    $journeyNum++;
                }

                // исключаемые а/к-валидирующие перевозчики
                // TODO: Отделаться от global
                if (in_array((string)$flight->company, $CONFIG[sirena_exclude_airlines])) {
                    continue 2;
                }

                // Номер плеча
                $segment->setJourneyNumber($journeyNum);

                // Код авиакомпании
                $segment->setOperationCompanyCode((string)$flight->company);
                // Код авиакомпании
                $segment->setMarketingCompanyCode((string)$flight->company);

                //$segment->setAircraft()

                /*if (trim((string)$flight->franchise_company)) {
                    $segment->setMarketingCompanyCode((string)$flight->franchise_company);
                }*/

                // Код транспортного средства
                $segment->setAircraftCode((string)$flight->airplane);

                // Номер рейса (Хотя в документации flight (но примере и ответе num))
                $segment->setFlightNumber((string)$flight->num);

                // Код города/порта отправления
                $segment->setDepartureCode((string)$flight->origin);
                $segment->setDepartureTerminal((string)$flight->origin["terminal"]);

                // Дата отправления
                list ($d,$m,$y) = explode(".",$flight->deptdate);
                $segment->setDepartureDate(new RK_Core_Date("20".$y."-".$m."-".$d . ' ' . (string) $flight->depttime, RK_Core_Date::DATE_FORMAT_DB));

                // Код города/порта прибытия
                $segment->setArrivalCode((string)$flight->destination);
                $segment->setArrivalTerminal((string)$flight->destination["terminal"]);

                // Время прибытия
                list ($d,$m,$y) = explode(".",$flight->arrvdate);
                $segment->setArrivalDate(new RK_Core_Date("20".$y."-".$m."-".$d. ' '. (string) $flight->arrvtime, RK_Core_Date::DATE_FORMAT_DB));

                // Доступный подкласс бронирования
                $segment->setClass($this->requestClass/*(string)$flight->subclass*/);
                $segment->setBaseClass((string)$flight->class);
                //$segment->setBaseClass((string)$flight->subclass);
                //$segment->setClass($this->requestClass);


                // Количество мест на данном подклассе бронирования.
                if (trim((string)$flight->available)) {
                    $segment->addAllowedSeat($segment->getBaseClass(), (string)$flight->available);
                    //var_dump((string)$flight->num, $segment->getBaseClass(), (string)$flight->available);
                }

                // Продожительность полета
                $segment->setFlightTime(AviaToMinutes((string)$flight->flightTime));
                //$segment->setBaggageMeasure()

                $booking->addSegment($segment);

                $segmentFarePrices[] = $flight->price;
            }

            //exit;

            // Суммируем стоимость сегмента для каждого типа пассажира (многа букв писать почему так и почему здесь, но, к сожалению так надо)
            $passengers = array();
            foreach ($segmentFarePrices as $segmentNum => $prices) {

                foreach ($prices as $price) {

                    // Пропускаем младенцев, они летят бесплатно
                    if (!$price->tax) { continue; }

                    // Сомнительная установка (бывают разные компании у прайсов в одном сегменте?)
                    $booking->setValidatingCompany((string)$price["validating_company"]);

                    $passengerId = (string)$price["passenger-id"];

                    $type           = $typePassAssoc[(string)$price["orig_code"]];

                    $currency       = (string)$price["currency"];
                    $count          = (int)$price["count"];
                    $baseFareCode   = (string)$price->fare["code"];

                    preg_match("/([0-9]+)([a-zA-Z]+)/", (string)$price["baggage"], $matches);
                    $baggage        = $matches[1]; // Кол-во багажа
                    $baggageMeasure = $matches[2]; // Мера багажа

                    $baseFare       = new RK_Core_Money(floatval($price->fare)*$count, $currency);
                    $equivFare      = new RK_Core_Money(floatval($price->fare)*$count, $currency);
                    $totalFare      = new RK_Core_Money(floatval($price->total)*$count, $currency);

                    $upt = array();
                    if (isset($price->upt)) {
                        foreach ((array)$price->upt as $key => $value) {
                            $upt[$key] = (string)$value;
                        }
                    }

                    // Если еще небыло пассажара с таким кодом, создаем его
                    if (!isset($passengers[$type])) {
                        $passengers[$type] = array(
                            "currency"      => $currency,
                            "count"         => $count,
                            "baseFareCode"  => $baseFareCode,
                            "baseFare"      => $baseFare,
                            "equivFare"     => $equivFare,
                            "totalFare"     => $totalFare,
                            "baggage"       => $baggage,
                            "baggageMeasure"=> $baggageMeasure,
                            "upt"           => $upt
                        );
                    } else {
                        //$passengers[$type]["count"]+=$count;
                        $passengers[$type]["baseFare"]  = $passengers[$type]["baseFare"]->add($baseFare);
                        $passengers[$type]["equivFare"] = $passengers[$type]["equivFare"]->add($equivFare);
                        $passengers[$type]["totalFare"] = $passengers[$type]["totalFare"]->add($totalFare);
                    }

                    // Таксы
                    foreach ($price->tax as $tax) {
                        $code = (string) $tax['code'];
                        $tax = new RK_Core_Money(floatval($tax), $currency);

                        if (!isset($passengers[$type]["tax"][$code])) {
                            $passengers[$type]["tax"][$code] = $tax;
                        } else {
                            $passengers[$type]["tax"][$code] = $passengers[$type]["tax"][$code]->add($tax);
                        }
                    }
                }
            }

            // Цены

            foreach ($passengers as $type => $passenger) {

                $farePrice = new RK_Avia_Entity_Price();

                $farePrice->setType($type);
                $farePrice->setQuantity($passenger["count"]);
                $farePrice->setBaseFare($passenger["baseFare"]);
                $farePrice->setEquivFare($passenger["equivFare"]);
                $farePrice->setTotalFare($passenger["totalFare"]);

                //$farePrice->getBaseFare()

                $fareRule = new RK_Sirena_Entity_Rule();
                $fareRule->setUpt($passenger['upt']);

                $farePrice->addFareRule($fareRule);

                foreach ($passenger["tax"] as $code => $tax) {
                    $farePrice->addTax($code, $tax);
                }

                // Установка кода тарифа в прайс и сегмент
                // Как расчитывать $segmentNum???????????????

                $segmentNum = 0;
                foreach ($segmentFarePrices as $prices) {
                    $farePrice->addFare($segmentNum, $passenger["baseFareCode"]);
                    $booking->getSegment($segmentNum)->setCodeFare($passenger["baseFareCode"]);
                    $booking->getSegment($segmentNum)->setBaggage($passenger["baggage"]);
                    $booking->getSegment($segmentNum)->setBaggageMeasure($passenger["baggageMeasure"]);

                    $segmentNum++;
                }

                $booking->addPrice($farePrice->getType(), $farePrice);
            }



            $results[] = $booking;

        }

        return $results;

    }
}