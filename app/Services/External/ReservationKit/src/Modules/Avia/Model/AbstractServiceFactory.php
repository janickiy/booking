<?php

namespace ReservationKit\src\Modules\Avia\Model;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Settings;

/**
 * Фабрика получения необходимых инструментов сервисов
 */
abstract class AbstractServiceFactory /*extends MainAbstractServiceFactory*/
{
    abstract public function getSearchService();

    abstract public function getBookingService();

    abstract public function getTicketingService();

    abstract public function getUpdateService();
}
