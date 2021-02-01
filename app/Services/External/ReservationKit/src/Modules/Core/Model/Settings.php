<?php

namespace ReservationKit\src\Modules\Core\Model;

/**
 * Реестр настроек приложения
 *
 * Для доступа к настройкам используется доменное представление опций,
 * к примеру [kitconfig]databases.databaselist содержит список баз данных
 */
class Settings
{
    /**
     * Ветки реестра. Ветка по умолчанию располагается с индексом "_"
     *
     * @var array
     */
    private static $_instances = array();

    /**
     * Возвращет ветку настроек или создает ее
     *
     * @param string $domain домен ветки
     * @return Settings
     */
    public static function getInstance($domain = '_')
    {
        if (!isset(self::$_instances[$domain])) {
            self::$_instances[$domain] = new Settings();
        }

        return self::$_instances[$domain];
    }

    public function __construct()
    {
        $this->_parameters = new \stdClass();
    }

    /**
     * Возвращает опцию по названию
     * 
     * @param string $domain название опции, к примеру application.router
     * @return mixed
     */
    public function get($domain = null)
    {
        $result = & $this->_parameters;
        if (isset($domain)) {
            foreach (explode('.', $domain) as $subDomain) {
                if (!isset($result->$subDomain)) {
                    return null;
                }
                $result = & $result->$subDomain;
            }
        }
        
        return $result;
    }

    /**
     * Устнавливает значение опции
     * 
     * @param string $domain название опции, к примеру application.router
     * @param mixed $value
     */
    public function set($domain, $value)
    {
        $branch = & $this->_parameters;
        foreach (explode('.', $domain) as $subDomain) {
            if (!isset($branch->$subDomain)) {
                $branch->$subDomain = new \stdClass();
            }
            $branch = & $branch->$subDomain;
        }
        
        $branch = $value;
    }
    
    /**
     * Возвращает список всех экземпляров настроек
     * 
     * Метод должен использоваться только в отладочных целях
     * 
     * @deprecated debug
     * @return array
     */
    public static function getAll()
    {
        return self::$_instances;
    }
}