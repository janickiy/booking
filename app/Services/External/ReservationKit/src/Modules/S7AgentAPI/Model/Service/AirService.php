<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model\Service;

// TODO
use ReservationKit\src\Modules\Avia\Model\Abstracts\Service;
use ReservationKit\src\Modules\Core\Model\Entity\CurrencyRates;

use ReservationKit\src\Modules\S7AgentAPI\Model\Entity\Booking as S7AgentBooking;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\AirDocIssueRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\AirDocVoidRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\AirShoppingRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\FlightPriceRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\ItinReshopRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\OrderCreateRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\OrderCancelRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\Request\OrderRetrieveRQ;
use ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser\AirDocDisplayParser;
use ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser\AirShoppingParser;
use ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser\FlightPriceParser;
use ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser\ItinReshopParser;
use ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser\OrderCreateParser;
use ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser\OrderCancelParser;

use ReservationKit\src\Modules\S7AgentAPI\Model\S7AgentException;

use ReservationKit\src\Modules\Galileo\Model\Entity\SearchRequest as GalileoSearchRequest;
use ReservationKit\src\Modules\Galileo\Model\Entity\Booking as GalileoBooking;
use ReservationKit\src\Modules\Galileo\Model\Entity\Price;
use ReservationKit\src\Modules\Galileo\Model\Requisites;

use ReservationKit\src\Modules\Galileo\Model\Exception\FailTicketException;
use ReservationKit\src\Modules\Galileo\Model\Exception\BookingCanceledException;
use ReservationKit\src\Modules\Galileo\Model\Enum\ModifyEnum;

use ReservationKit\src\Modules\S7AgentAPI\Model\ResponseParser\OrderViewParser;
use ReservationKit\src\RK;

class AirService extends Service
{
    public function search(\RK_Avia_Entity_Search_Request $searchRequest)
    {

        // Получения списка систем разрешенных для поиска

        // Запуск многопоточного поиска в системах бронирования

        // Фильтрация результатов

        //
        try {
            // Поисковый запрос
            $request = new AirShoppingRQ($searchRequest);
            $response = $request->send();

            // Данные для отображения логов
            //$this->_logs = $request->getLogs();

            // Парсер ответа
            if ($response) {
                $parser = new AirShoppingParser($response);
                $parser->parse();

                $this->setResult($parser->getResult());

                return $parser->getResult();
            }

        } catch (\Exception $e) { throw new \RK_Core_Exception($e->getMessage()); }

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

    /**
     * TODO переименовать в pricing
     * @param \RK_Avia_Entity_Search_Request|S7AgentBooking $searchRequest
     * @return mixed|null
     */
    public function price($searchRequest)
    {
        $prices = $this->_priceOne($searchRequest);

        foreach ($searchRequest->getSegments() as $segment) {
            $fareCode = $segment->getFareCode();

            // Замена кода тарифа BASIC на FLEX
            if (substr($fareCode, 1, 2) === 'BS') {
                $segment->setFareCode(str_replace('BS', 'FL', $fareCode));
            }

            // Замена кода тарифа FLEX на BASIC
            if (substr($fareCode, 1, 2) === 'FL') {
                $segment->setFareCode(str_replace('FL', 'BS', $fareCode));
            }
        }

        $brandPrices = $this->_priceOne($searchRequest);

        $priceSolutions = array_merge($prices, $brandPrices);

        return $priceSolutions;
    }

    private function _priceOne($searchRequest)
    {
        try {
            // Запрос прайсинга
            $request = new ItinReshopRQ($searchRequest);
            $response = $request->send();

            // Парсинг ответа
            if ($response && !$searchRequest instanceof S7AgentBooking) {
                $parser = new ItinReshopParser($response);
                $parser->parse();

                // Добавление курсов валют в прайсы
                //$this->addCurrencyRatesToPrices($parser->getResult());

                return $parser->getResult();
            }

        } catch (\Exception $e) {

        }

        return null;
    }

    public function booking(\RK_Avia_Entity_Booking $booking)
    {
        try {
            // Поисковый запрос
            $request  = new OrderCreateRQ($booking);
            $response = $request->send();

            if ($response) {
                // Парсер ответа
                $parser = new OrderCreateParser($response);
                $parser->setBooking($booking);
                $parser->parse();

                // Проверка наличия ошибок при создании бронирования
                /*
                foreach ($parser->getListErrorMessage() as $errorMessage) {
                    if ($booking->getStatus() === \RK_Avia_Entity_Booking::STATUS_BOOKED && $errorMessage === '*0 AVAIL/WL CLOSED*') {
                        $this->cancel($booking);

                        throw new BookingCanceledException();
                    }
                }
                */

                // Добавление курсов валют в прайсы
                //$this->addCurrencyRatesToPrices(array($booking->getPrices()));

                return true;
            }

        } catch (BookingCanceledException $e) {
            $textMessages = implode('<br/>', $booking->getErrorMessages());
            $textMessages = 'Бронирование было автоматически отменено по причине: ' . $textMessages;

            throw new S7AgentException($textMessages);

        } catch (\Exception $e) {
            $listErrorMessage = $parser->getListErrorMessage();

            if (!empty($listErrorMessage)) {
                $message = implode('; ', $parser->getListErrorMessage());
            } else {
                $message = $e->getMessage();
            }

            throw new S7AgentException($message);
        }

        return false;
    }

    /**
     * @param \RK_Avia_Entity_Booking $booking
     * @return array
     * @throws S7AgentException
     * @throws \ReservationKit\src\Modules\Galileo\Model\GalileoException
     */
    public function read(\RK_Avia_Entity_Booking $booking)
    {
        try {
            // Поисковой запрос
            $request  = new OrderRetrieveRQ($booking);
            $response = $request->send();

            // Парсер ответа
            if ($response) {
                $parser = new OrderViewParser($response);
                $parser->setBooking($booking);
                $parser->parse();
            }

        } catch (\RK_Sabre_Exception_Search_NoResults $e) { /* Fixme Sabre в Галилео */ }

        return array();
    }

    public function cancel(\RK_Avia_Entity_Booking $booking)
    {
        try {
            $request = new OrderCancelRQ($booking);
            $response = $request->send();

            $response = new OrderCancelParser($response);
            $response->setBooking($booking);
            $response->parse();

        } catch (\Exception $e) {  }

        return $booking;
    }

    public function ticket(S7AgentBooking $booking)
    {
        try {

            $request = new AirDocIssueRQ($booking/*, $passenger*/);
            $response = $request->send();

            $response = new AirDocDisplayParser($response);
            $response->setBooking($booking);
            $response->parse();

            /*
            foreach ($booking->getPassengers() as $passenger) {
                $request = new AirDocIssueRQ($booking, $passenger);
                $response = $request->send();

                $response = new AirDocDisplayParser($response);
                $response->setBooking($booking);
                $response->setPassenger($passenger);
                $response->parse();
            }
            */

        } catch (FailTicketException $e) {
            $listErrorMessage = $response->getListErrorMessage();

            if (!empty($listErrorMessage)) {
                $message = implode('; ', $response->getListErrorMessage());
            } else {
                $message = $e->getMessage();
            }

            throw new S7AgentException($message);

        } catch (\Exception $e) {
            // Неизвестная ошибка
            throw new S7AgentException($e->getMessage());
        }

        return $booking;
    }

    public function retrieveTicket(S7AgentBooking $booking)
    {
        if ($booking->getStatus() === \RK_Avia_Entity_Booking::STATUS_TICKET) {
            foreach ($booking->getPassengers() as $passenger) {
                $passenger->getTicketNumbers();
            }
        }

        return false;
    }

    public function voidTicket($ticketNumber)
    {
        try {
            // Поисковой запрос
            $request  = new AirDocVoidRQ($ticketNumber);
            $response = $request->send();

            // Парсер ответа
            if ($response) {
                //$parser = new OrderViewParser($response);
                //$parser->setBooking($booking);
                //$parser->parse();
            }

        } catch (\Exception $e) { /* Fixme Sabre в Галилео */ }

        return array();
    }

    public function getRules($searchRequestOrBooking)
    {
        try {
            // Поисковой запрос
            $request  = new FlightPriceRQ($searchRequestOrBooking);
            $response = $request->send();

            // Парсер ответа
            if ($response) {
                $parser = new FlightPriceParser($response);

                return $parser->parse();
            }

        } catch (\Exception $e) {  }

        return array();
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