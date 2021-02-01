<?php

namespace ReservationKit\src\Modules\Base\Model\Entity;

use ReservationKit\src\Modules\Galileo\Model\Entity\ProviderRequisitesRules;

/**
 * Класс для работы с параметрами поставщика услуг
 */
class ProviderRequisites
{
    /**
     * Название поставщика услуг (gabriel, galileo, sirena ...)
     *
     * @var string
     */
    private $_system;

    /**
     * Содержит в себе правила работы поставщика, настройки системы, правила для каждого поставщика свои
     *
     * @var ProviderRequisitesRules
     */
    private $_rules;

    /**
     * Возвращает название поставщика услуг
     *
     * @return string
     */
    public function getSystem()
    {
        return $this->_system;
    }

    /**
     * Устанавливает название поставщика услуг
     *
     * @param string $system
     */
    public function setSystem($system)
    {
        $this->_system = $system;
    }

    /**
     * Возвращает правила поставщика услуг
     *
     * @return ProviderRequisitesRules
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * Устанавливает правила поставщика услуг
     *
     * @param ProviderRequisitesRules $rules
     */
    public function setRules(ProviderRequisitesRules $rules)
    {
        $this->_rules = $rules;
    }
}