<?php

namespace ReservationKit\src\Modules\Sirena\Model;

use ReservationKit\src\Modules\Base\Model\Abstracts\AbstractRequisites;
use ReservationKit\src\Modules\Sirena\Model\RequisiteRules;

/**
 * ГРУ (учебная)	193.104.87.251:34323 или 194.84.25.50:34323
 * ГРС (рабочая)	193.104.87.251:34321 или 194.84.25.50:34321
 * ГРТ (тестовая)	193.104.87.251:34322 или 194.84.25.50:34322
 *
 * @package ReservationKit\src\Modules\Sirena\Model
 */
class Requisites extends AbstractRequisites
{
    /**
     * Типы среды.
     */
    const ENV_TYPE_GRU = 'gru'; //ГРУ учебный
    const ENV_TYPE_GRS = 'grs'; //ГРС рабочая
    const ENV_TYPE_GRT = 'grt'; //ГРТ тестовый

    private $_address = '193.104.87.251';   // 194.84.25.50

    private $_ports = array(
        self::ENV_TYPE_GRU => 34323,
        self::ENV_TYPE_GRT => 34322,
        self::ENV_TYPE_GRS => 34321
    );

    /** @var Requisites Экземпляр объекта */
    private static $_instance;

    private function __construct() {  }  // Защищаем от создания через new Singleton
    private function __clone()     {  }  // Защищаем от создания через клонирование
    //private function __wakeup()    {  }  // Защищаем от создания через unserialize

    /**
     * Возвращает единственный экземпляр класса
     *
     * @param RequisiteRules|null $rules
     * @return Requisites
     */
    public static function getInstance(/*RequisiteRules*/ $rules = null)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
            self::$_instance->setEnvironment(self::ENV_TEST);
        }

        /*
        if ($rules instanceof RequisiteRules) {
            self::$_instance->setRules($rules);
        }
        */

        return self::$_instance;
    }

    /**
     * Получаем порт
     *
     * @param string $type
     * @return string
     */
    public function getPort($type = '')
    {
        if (!$type || !isset($this->_ports[$type])) {
            $type = self::ENV_TYPE_GRT;
        }

        return $this->_ports[$type];
    }

    /**
     * Хост
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_address;
    }

    /**
     * Устанавливает правила поиска и выписки для системы броинрования
     *
     * @return RequisiteRules
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * Устанавливает правила поиска и выписки для системы броинрования
     *
     * @param RequisiteRules $rules
     */
    public function setRules(RequisiteRules $rules)
    {
        $this->_rules = $rules;
    }

    /**
     * Возвращает название системы бронирования
     * TODO перенести в настройки/config модуля
     * @return string
     */
    public function getSystemName()
    {
        return 'Sirena';
    }
}