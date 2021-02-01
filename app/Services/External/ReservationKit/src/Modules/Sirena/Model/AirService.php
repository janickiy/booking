<?php

namespace ReservationKit\src\Modules\Sirena\Model;

use ReservationKit\src\Modules\Avia\Model\Abstracts\Service;

use ReservationKit\src\Modules\Sirena\Model\Request\Pricing;

use ReservationKit\src\Modules\Sirena\Model\ResponseParser\PricingParser;

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
            $request = new Pricing($searchRequest);
            $response = $request->send();

            // Данные для отображения логов
            $this->_logs = $request->getLogs();

            // Парсер ответа
            if ($response) {
                $parser = new PricingParser($response);
                $parser->parse();

                pr($parser->getResult());
                die('*711');

                $this->setResult($parser->getResult());

                return $parser->getResult();
            }

        } catch (\Exception $e) { throw new \RK_Core_Exception($e->getMessage()); }

        return array();
    }

    public function pricing()
    {

    }

    public function booking()
    {

    }

    public function read()
    {

    }

    public function cancel()
    {

    }

    public function ticketing()
    {

    }

    public function getRules()
    {

    }
}