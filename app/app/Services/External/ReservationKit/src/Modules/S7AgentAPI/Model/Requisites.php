<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model;

use ReservationKit\src\Modules\Avia\Model\Entity\Search\Settings;
use ReservationKit\src\Modules\Base\Model\Abstracts\AbstractRequisites;
use ReservationKit\src\Modules\Galileo\Model\RequisiteRules;

class Requisites extends AbstractRequisites
{
    // PT-BR (Portuguese Brazil), FR-CA (French Canadian), ZH-HANT (Chinese Traditional), ZH-HANS (Chinese Simplified)
    const LANGUAGE_CODE_RU = 'RURU';
    const LANGUAGE_CODE_GB = 'ENGB';
    const LANGUAGE_CODE_US = 'ENUS';

    const CURRENCY_RUB = 'RUB';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';

    const WSDL_PATH = '/agent-api/wsdl/0.35?wsdl';

    private $_credentials = array(
        self::ENV_PROD => array('login' => '', 'password' => ''),
        self::ENV_TEST => array('login' => 'trivago', 'password' => '5rUcADUs7uku')
    );

    private $_host = array(
        self::ENV_PROD => 'http://api.s7.ru',
        self::ENV_TEST => 'http://qa-gaia.s7.ru',
    );

    /** @var Requisites Экземпляр объекта */
    private static $_instance;

    /** @var RequisiteRules */
    private $_rules;

    private function __construct() {  }  // Защищаем от создания через new Singleton
    private function __clone()     {  }  // Защищаем от создания через клонирование
    //private function __wakeup()    {  }  // Защищаем от создания через unserialize

    /**
     * Возвращает единственный экземпляр класса
     *
     * @param RequisiteRules|null $rules
     * @return Requisites
     */
    public static function getInstance(RequisiteRules $rules = null)
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
            self::$_instance->setEnvironment(self::ENV_PROD);
        }

        if ($rules instanceof RequisiteRules) {
            self::$_instance->setRules($rules);
        }

        return self::$_instance;
    }

    /**
     * Возвращает идентификатор пользователя
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->_credentials[$this->getEnvironment()]['login'];
    }

    /**
     * Возвращает пароль окружения
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_credentials[$this->getEnvironment()]['password'];
    }

    /**
     * Возвращает url отправки запросов
     *
     * @return string
     */
    public function getRequestURI()
    {
        return $this->_host[$this->getEnvironment()];
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
     *
     * @return string
     */
    public function getSystemName()
    {
        return 's7agent';
    }
}