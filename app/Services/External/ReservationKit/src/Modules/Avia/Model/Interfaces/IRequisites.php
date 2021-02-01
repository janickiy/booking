<?php
namespace ReservationKit\src\Modules\Avia\Model\Interfaces;

use ReservationKit\src\Modules\Galileo\Model\RequisiteRules as GalileoRequisiteRules;

interface IRequisites
{
    /**
     * Возвращает название системы бронирования
     *
     * @return string
     */
    public function getSystemName();

    /**
     * Возвращает правила применения реквизитов
     *
     * @return GalileoRequisiteRules|null
     */
    public function getRules();
}