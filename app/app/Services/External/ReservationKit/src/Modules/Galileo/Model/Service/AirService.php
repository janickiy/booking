<?php

namespace ReservationKit\src\Modules\Galileo\Model\Service;

use ReservationKit\src\Modules\Avia\Model\Abstracts\Service;
use ReservationKit\src\Modules\Core\Model\Entity\CurrencyRates;
use ReservationKit\src\Modules\Galileo\Model\Entity\SearchRequest as GalileoSearchRequest;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price;
use ReservationKit\src\Modules\Galileo\Model\Request\AirFareRulesReq;
use ReservationKit\src\Modules\Galileo\Model\Request\AirPriceReq;
use ReservationKit\src\Modules\Galileo\Model\Request\GdsQueuePlaceReq;
use ReservationKit\src\Modules\Galileo\Model\Request\CurrencyConversionReq;
use ReservationKit\src\Modules\Galileo\Model\Request\UniversalRecordModifyReq;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\Response\AirFareRulesRsp;
use ReservationKit\src\Modules\Galileo\Model\Response\AirPriceRsp;
use ReservationKit\src\Modules\Galileo\Model\Request\LowFareSearchReq;
use ReservationKit\src\Modules\Galileo\Model\Response\CurrencyConversionParser;
use ReservationKit\src\Modules\Galileo\Model\Response\LowFareSearchRsp;
use ReservationKit\src\Modules\Galileo\Model\Request\AvailabilitySearchReq;
use ReservationKit\src\Modules\Galileo\Model\Response\AvailabilitySearchRsp;
use ReservationKit\src\Modules\Galileo\Model\Request\AirCreateReservationReq;
use ReservationKit\src\Modules\Galileo\Model\Response\AirCreateReservationRsp;
use ReservationKit\src\Modules\Galileo\Model\Request\UniversalRecordRetrieveReq;
use ReservationKit\src\Modules\Galileo\Model\Response\UniversalRecordRetrieveRsp;
use ReservationKit\src\Modules\Galileo\Model\Request\UniversalRecordCancelReq;
use ReservationKit\src\Modules\Galileo\Model\Response\UniversalRecordCancelRsp;
use ReservationKit\src\Modules\Galileo\Model\Request\AirTicketingReq;
use ReservationKit\src\Modules\Galileo\Model\Response\AirTicketingRsp;

use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Exception\FailTicketException;
use ReservationKit\src\Modules\Galileo\Model\Exception\BookingCanceledException;
use ReservationKit\src\Modules\Galileo\Model\Enum\ModifyEnum;
use ReservationKit\src\RK;

class AirService extends Service
{
    private $_logs;

    /**
     * @return mixed
     */
    public function getLogs()
    {
        return $this->_logs;
    }

    /**
     * @param $result
     * @return bool
     */
    public function addResult($result)
    {
        if (empty($this->getResult())) {
            $this->setResult($result);

            return true;
        }

        foreach ($this->getResult() as $PCC => $waySolutions) {
            if (!empty($result[$PCC])) {
                $newResultForPCC = array();

                foreach ($waySolutions as $numWay => $variantsSolution) {
                    if (isset($result[$PCC][$numWay])) {
                        $newResultForPCC[$numWay] = array_merge($variantsSolution, $result[$PCC][$numWay]);
                    } else {
                        $newResultForPCC[$numWay] = $variantsSolution;
                    }
                }

                $this->setResult(array($PCC => $newResultForPCC));
            }
        }
    }

    public function search(\RK_Avia_Entity_Search_Request $searchRequest)
    {	
        /* @var GalileoSearchRequest $searchRequest */

        // Получения списка систем разрешенных для поиска

        // Запуск многопоточного поиска в системах бронирования

        // Фильтрация результатов

        //
		try {
            // Поисковый запрос
            $request = new LowFareSearchReq($searchRequest);
            $request->send();

            // Парсер ответа
            if ($request->getResponse()) {
                $parser = new LowFareSearchRsp($request->getResponse());
                $parser->parse();

                if (is_array($parser->getResult())) {
                    /** @var \RK_Avia_Entity_Booking $item */
                    foreach ($parser->getResult() as $item) {
                        $item->setRequisiteId($searchRequest->getRequisiteId());
                    }
                }

                $this->setResult($parser->getResult());

                return $parser->getResult();
            }

        } catch (\Exception $e) { /*throw new \RK_Core_Exception($e->getMessage());*/ }

        return array();
    }

    public function availability(\RK_Avia_Entity_Search_Request $searchRequest)
    {
        /* @var GalileoSearchRequest $searchRequest */

        // Получения списка систем разрешенных для поиска

        // Запуск многопоточного поиска в системах бронирования

        // Фильтрация результатов

        //

        try {
            $this->_logs = array('isAvail' => true);
            $i = 0;

            do {
                // Поисковый запрос
                $request = new AvailabilitySearchReq($searchRequest);
                $request->send();

                // Данные для отображения логов
                $this->_logs[] = $request->getLogs();

                // Парсер ответа
                if ($request->getResponse()) {
                    $parser = new AvailabilitySearchRsp($request->getResponse());
                    $parser->parse();

                    $searchRequest->setNextResultReference($parser->getNextResultReference());

                    $this->addResult($parser->getResult());
                }

                $i++;

            } while (!empty($searchRequest->getNextResultReference()) && $i < 10);

            return $this->getResult();

        } catch (\Exception $e) { /*throw new \RK_Core_Exception($e->getMessage());*/ }

        return array();
    }

    public function price(\RK_Avia_Entity_Search_Request $searchRequest)
    {
        try {
            // Запрос прайсинга
            $request = new AirPriceReq($searchRequest);
            $request->send();

            // Парсинг ответа
            if ($request->getResponse()) {
                $parser = new AirPriceRsp($request->getResponse());
                $parser->parse();

                // Добавление курсов валют в прайсы
                $this->addCurrencyRatesToPrices($parser->getResult());

                return $parser->getResult();
            }

        } catch (\Exception $e) {  }

        return null;
    }

    public function booking(\RK_Avia_Entity_Booking $booking)
    {
        try {
            // Поисковый запрос
            $request  = new AirCreateReservationReq($booking);
            $response = $request->send();

            if ($response) {
                // Парсер ответа
                $parser = new AirCreateReservationRsp($response);
                $parser->setBooking($booking);
                $parser->parse();

                // Проверка наличия ошибок при создании бронирования
                foreach ($parser->getListErrorMessage() as $errorMessage) {
                    if ($booking->getStatus() === \RK_Avia_Entity_Booking::STATUS_BOOKED && $errorMessage === '*0 AVAIL/WL CLOSED*') {
                        $this->cancel($booking);

                        throw new BookingCanceledException();
                    }
                }

                // Добавление курсов валют в прайсы
                $this->addCurrencyRatesToPrices(array($booking->getPrices()));

                // Добавление брони в очередь International SOS
                /* @var GalileoBooking $booking */
                if ($booking->isInternationalSOS()) {
                    $request = new GdsQueuePlaceReq($booking);
                    $request->send();
                }

                return true;
            }

        } catch (BookingCanceledException $e) {
            $textMessages = implode('<br/>', $booking->getErrorMessages());
            $textMessages = 'Бронирование было автоматически отменено по причине: ' . $textMessages;

            throw new GalileoException($textMessages);

        } catch (\Exception $e) {
            if (isset($response, $parser)) {
                $listErrorMessage = $parser->getListErrorMessage();
            }

            if (!empty($listErrorMessage)) {
                $message = implode('; ', $parser->getListErrorMessage());
            } else {
                $message = $e->getMessage();
            }

            throw new GalileoException($message);
        }

        return false;
    }

    /**
     * @param \RK_Avia_Entity_Booking|Booking $booking
     * @return array|null
     * @throws Exception
     */
    public function read(\RK_Avia_Entity_Booking $booking)
    {
        try {
            // Фиксация курса валюты. Этот курс будет использоваться после выписки брони
            $fixedPrices = $booking->getPrices();

            // Поисковой запрос
            $request  = new UniversalRecordRetrieveReq($booking);
            $response = $request->send();

            // Парсер ответа
            if ($response) {
                $parser = new UniversalRecordRetrieveRsp($response);
                $parser->setBooking($booking);
                $parser->parse();

                // Установка курсов валют
                if ($booking->getStatus() !== \RK_Base_Entity_Booking::STATUS_TICKET && $booking->getStatus() !== \RK_Base_Entity_Booking::STATUS_CANCEL) {
                    // Обновление курсов валют в прайсах, если бронь не выписана или не отменена
                    $this->addCurrencyRatesToPrices(array($booking->getPrices()));

                } else {
                    $currencyRK  = RK::getContainer()->getAppCurrency();
                    $currencyWAB = Requisites::getInstance()->getCurrencyWAB();

                    if ($currencyRK !== $currencyWAB) {
                        if (isset($fixedPrices)) {
                            // Установка старых курсов валют
                            $updatedPrices = $booking->getPrices();

                            foreach ($updatedPrices as $updatedTypePassengerKey => $updatedPrice) {
                                foreach ($fixedPrices as $fixedTypePassengerKey => $fixedPrice) {
                                    if ($updatedTypePassengerKey === $fixedTypePassengerKey) {
                                        $updatedPrice->setCurrencyRates($fixedPrice->getCurrencyRates());
                                        continue;
                                    }
                                }
                            }

                        } else {
                            // Если не установлены старые курсы валют, то добавляем новые курсы
                            $this->addCurrencyRatesToPrices(array($booking->getPrices()));
                        }
                    }
                }
            }

        } catch (\RK_Sabre_Exception_Search_NoResults $e) { /* Fixme Sabre в Галилео */ }

        return array();
    }

    public function modify(GalileoBooking $booking, ModifyEnum $modifyMethod)
    {
        try {
            // Поисковый запрос
            $request  = new UniversalRecordModifyReq($booking, $modifyMethod);
            $response = $request->send();

        } catch (\RK_Sabre_Exception_Search_NoResults $e) {  }

        return false;
    }

    public function cancel(\RK_Avia_Entity_Booking $booking)
    {
        try {
            $request = new UniversalRecordCancelReq($booking);
            $response = $request->send();

            $response = new UniversalRecordCancelRsp($response);
            $response->setBooking($booking);
            $response->parse();

        } catch (\Exception $e) {  }

        return $booking;
    }

    public function ticket(\RK_Avia_Entity_Booking $booking)
    {
        try {
            $request = new AirTicketingReq($booking);
            $response = $request->send();

            $response = new AirTicketingRsp($response);
            $response->setBooking($booking);
            $response->parse();

        } catch (FailTicketException $e) {
            $listErrorMessage = $response->getListErrorMessage();

            if (!empty($listErrorMessage)) {
                $message = implode('; ', $response->getListErrorMessage());
            } else {
                $message = $e->getMessage();
            }

            throw new GalileoException($message);

        } catch (\Exception $e) {
            // Неизвестная ошибка
            throw new GalileoException($e->getMessage());
        }

        return $booking;
    }

    /**
     * Правила тарифов
     *
     * @param \RK_Avia_Entity_Booking $booking
     * @return array
     */
    public function getRules($booking)
    {
        $prices = $booking->getPrices();
        if (isset($prices['ADT']) && $prices['ADT']) {
            $price = $prices['ADT'];

            /* @var array $fareInfo */
            $fareInfo = $price->getFareInfo();
            $fareInfo = current($fareInfo);

            $keyRef  = $fareInfo->getKey();
            $keyRule = $fareInfo->getRuleKey();

            try {
                $request = new AirFareRulesReq($keyRef, $keyRule);
                $response = new AirFareRulesRsp($request->send());
                $response->parse();
                $rules = $response->getResult();

                // Правила по сегментам
                /*
                $result = array(
                    //'passengerType' => $passengerType,
                    //'itinerary' => $fare->getDepartureAirportCode() . '/' . $fare->getArrivalAirportCode(),
                    'text' => $rules[16]
                );
                */

                // Возвращается только 16 правило
                return array($rules[16]);

            } catch (\Exception $e) {

            }
        }
    }

    /**
     * Добавляет курсы валют, если они нужны
     *
     * Внимание! Объект курсов валют един для всех прайсов (ADT, CHD, INF и тд).
     * Изменяя курсы валют у прайса ADT он измениться и у остальных. В пределах брони.
     *
     * @param array $priceSolutions
     * @return array
     * @throws \Exception
     * @throws \RK_Core_Exception
     */
    private function addCurrencyRatesToPrices(array $priceSolutions)
    {
        $currencyRK  = RK::getContainer()->getAppCurrency();
        $currencyWAB = Requisites::getInstance()->getCurrencyWAB();

        if ($currencyRK !== $currencyWAB) {
            $currencyConversionReq = new CurrencyConversionReq($currencyWAB, $currencyRK);
            $currencyConversionRsp = $currencyConversionReq->send();

            $parser = new CurrencyConversionParser($currencyConversionRsp);
            $currencyRates = $parser->parse();

            foreach ($priceSolutions as $costSolution => $prices) {
                /* @var $price Price */
                foreach ($prices as $typePassenger => $price) {
                    $price->setCurrencyRates($currencyRates);
                }
            }
        }

        return $priceSolutions;
    }
}