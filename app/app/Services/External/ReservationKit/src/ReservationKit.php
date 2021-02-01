<?php

namespace ReservationKit\src;

use ReservationKit\src\Modules\Core\Model\Container;

/**
 * Главный класс приложения
 *
 * @package ReservationKit\src
 */
class RK
{
    /**
     * Контейнер приложения
     *
     * - Адптеры подключения к БД    [db.list]
     * - Модули
     * - Языки
     *
     * @var Container
     */
    private static $_container;

    /**
     * Класс используется только в статическом контексте, поэтому
     * использование конструктора недоступно
     *
     * @ignore
     * @codeCoverageIgnore
     */
    private function __construct() {}

    /**
     * Производит инициализацию приложения
     *
     * @param array $config массив базовых настроек приложения. Обязателен только при первом вызове
     * @return Container
     * @throws \Exception
     */
    public static function init($config)
    {
        if (!isset(self::$_container)) {
            self::$_container = new Container();
            // TODO создать специальный класс Loader, с помощью которого делать загрузку в контейнер
            self::getContainer()->loadConfig($config);  // Загрузка данных из $config в объект Settings()
            self::getContainer()->loadBundles();
            self::getContainer()->loadModules();
            
            //self::$_container->loadKits();
            //self::$_container->getModule('Core')->fireEvent(new RK_Core_Event_AppLoad($this));
            
            //self::$_container->init($config);
        }

        return self::$_container;
    }

    /**
     * Запускает исполнителя параллельной задачи по названию
     * 
     * Название задачи состоит из названия бандла и имени файла-worker'a
     * Например, по имени avia.search будет запущен worker AviaBundle/Resources/worker/search.php 
     * 
     * @param string $workerName название исполнителя
     * @throws \RK_Core_Exception
     */
    public static function executeWorker($workerName)
    {
        $bundleName = strtok(ucfirst(strtolower($workerName)), '.') /*. 'Bundle'*/;
        $workerName = strtok('.');
        
        if ($workerName = self::getContainer()->getBundle($bundleName)->getFileWorker($workerName)) {
            include $workerName;
            
        } else {
            throw new \RK_Core_Exception('Invalid worker name');
        }
    }

    /**
     * Возвращает текущий контекст
     *
     * @return Container
     */
    public static function getContainer()
    {
        return self::$_container;
    }
}