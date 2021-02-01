<?php

namespace ReservationKit\src\Bundle\AviaBundle;

use ReservationKit\src\Bundle\AviaBundle\Service\SearchService;
use ReservationKit\src\Component\Framework\Bundle\Bundle;

class AviaBundle extends Bundle
{
    public function getFileWorker($workerName)
    {
        $fileName = preg_replace('/[^a-z0-9\.]/', '', $workerName);
        $fileName = str_replace('.', '/', $fileName);
        $fileName = $this->getPath() . '/Resources/worker/' . $fileName . '.php';

        if (file_exists($fileName)) {
            return $fileName;
        }
        
        return null;
    }

    /**
     * @return SearchService
     */
    public function getService()
    {
        return new Service();
    }

    /**
     * @return SearchService
     */
    public function getSearchService()
    {
        return new SearchService();
    }
}