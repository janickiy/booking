<?php

namespace ReservationKit\src\Modules\Core\Model;

use ReservationKit\src\Component\DB\Adapter\MySQLAdapter;
use ReservationKit\src\Component\DB\Adapter\MySQLiAdapter;
use ReservationKit\src\Component\DB\Adapter\MSSQLAdapter;
use ReservationKit\src\Component\DB\Adapter\PgSQLAdapter;
use ReservationKit\src\Component\DB\Adapter\AbstractAdapter;
use ReservationKit\src\Component\Framework\Bundle\Bundle;
use ReservationKit\src\Modules\Core\Model\Config\Loader\ArrayLoader;

use ReservationKit\src\Modules\Core\Model\Enum\CurrencyEnum;
use ReservationKit\src\Modules\Core\Model\Enum\LanguageEnum;
use ReservationKit\src\Modules\Galileo\Model\Factory as GalileoFactory;

/**
 * Контейнер приложения
 *
 * Более расширенный класс по сравнению с RK.
 * Содержит обьекты модулей, событий, подключения к БД и т.д.
 *
 * Конфигурация приложения может быть передана как в виде файла, так и виде
 * массива. Массив является более приоритетным вариантом при выполнении.
 *
 */
class Container
{
    /**
     * Интерфейсы подключения к БД
     *
     * Формат array((string)'kitdb' => (class)'RK_Core_Source_Adapter_Mysql', ...)
     *
     * @var array
     * @see MySQLAdapter
     * @see AbstractAdapter
     */
    private $_dbAdapters;

    /**
     * @var array
     */
    private $_bundles;

    /**
     * @var array
     */
    private $_modules;

    /**
     * Выполняет установку базовых настроек библиотеки из массива
     *
     * @param $config
     * @throws \Exception
     */
    public function loadConfig($config)
    {
        if (is_array($config)) {
            $loader = new ArrayLoader();
            $config = $loader->readConfig($config);

            foreach ($config as $domain => $value) {
                Settings::getInstance('config')->set($domain, $value);
            }

        } else {
            throw new \RK_Core_Exception('Invalid kit config');
        }
    }

    /**
     * Выполняет загрузку бандлов
     *
     * Метод ожидает найти в папке бандла файл bootstrap.php,
     * указывающий класс модуля, зависимости и другие параметры
     * У класса модуля будет вызван метод load с передачей в него $this
     *
     */
    public function loadBundles()
    {
        //$this->_bundles['AviaBundle'] = '\ReservationKit\src\Bundle\AviaBundle\AviaBundle';

        $pathBundles = '\ReservationKit\src\Bundle\\';

        $modulesData = array();
        //$modulesOrder = array('Core' => PHP_INT_MAX);
        foreach (glob(RK_ROOT_PATH . '/Bundle/*') as $directory) {
            if (is_dir($directory)) {
                $bundleName = preg_replace('/.+?\/([A-Za-z0-9]+)$/', '$1', $directory);
                if (file_exists($directory . '/' . $bundleName . '.php')) {
                    if (1 /*|| $this->validateBundleData($classBundle)*/) {
                        // TODO оптимизировать загрузку классов Бандлов. Метод getBundle() - всегда возвращает новый объект. А объекты бандла плодить не надо
                        $this->_bundles[$bundleName] = $pathBundles . $bundleName . '\\' . $bundleName;

                        //$modulesData[$bundleName] = RK_ROOT_PATH . '\Bundle\\' . $bundleName . '\\' . $bundleName;
                        //$modulesOrder[$bundleName] = 0;

                    } else {
                        throw new \RK_Core_Exception("Invalid Bootstrap for [$bundleName]");
                    }
                }
            }
        }

        /*

        foreach ($modulesData as $data) {
            $required = isset($data['require']) ? explode(',', $data['require']) : array();
            foreach ($required as $module)
            {
                if ($module = trim($module))
                {
                    if (!isset($modulesOrder[$module]))
                    {
                        $modulesOrder[$module] = 0;
                    }
                    $modulesOrder[$module]++;
                }
            }
        }

        arsort($modulesOrder);
        foreach ($modulesOrder as $moduleName => $counter) {
            $loader = new $modulesData[$moduleName]['loader']();
            $loader->load($this);
            $this->addModule($moduleName, $loader);
        }

        */
    }

    public function loadModules()
    {
        $this->_modules['galileo'] = '\ReservationKit\src\Modules\Galileo\Model\Factory';
        $this->_modules['s7agent'] = '\ReservationKit\src\Modules\S7AgentAPI\Model\Factory'; // TODO с заглавной буквы из-за дефайна в бустрапе, привести все к единому стилю
        $this->_modules['sirena']  = '\ReservationKit\src\Modules\Sirena\Model\Factory';
    }

    /**
     * Возвращает интерфейс к нужной базе данных по типу источника
     *
     * @param string $dbDomain main // logs|objects|cache|settings
     * @return AbstractAdapter
     * @throws \RK_Core_Exception
     */
    public function getDbAdapterFor($dbDomain)
    {
        if (!isset($this->_dbAdapters[$dbDomain])) {
            $conf = Settings::getInstance('config');

            $databases = $conf->get('db.list');

            switch ($databases[$dbDomain]['type']) {
                case 'mssql':
                    $this->_dbAdapters[$dbDomain] = new MSSQLAdapter($databases[$dbDomain]);
                    break;
                case 'pdo':
                    //$this->_dbAdapters[$dbDomain] = new PDOAdapter($databases[$dbDomain]);
                    break;
                case 'mysqli':
                    $this->_dbAdapters[$dbDomain] = new MySQLiAdapter($databases[$dbDomain]);
                    break;
                case 'mysql':
                    $this->_dbAdapters[$dbDomain] = new MySQLAdapter($databases[$dbDomain]);
                    break;
                case 'pgsql':
                    $this->_dbAdapters[$dbDomain] = new PgSQLAdapter($databases[$dbDomain]);
                    break;
                default:
                    throw new \RK_Core_Exception('Invalid database type');
            }
        }

        return $this->_dbAdapters[$dbDomain];
    }

    /**
     * @param $bundleName
     * @return Bundle
     * @throws \RK_Core_Exception
     */
    public function getBundle($bundleName)
    {
        if (isset($this->_bundles[$bundleName . 'Bundle'])) {
            return new $this->_bundles[$bundleName . 'Bundle']();
        }

        throw new \RK_Core_Exception("[" . $bundleName . "Bundle] bundle not found");
    }

    /**
     * @param $moduleName
     * @return GalileoFactory
     * @throws \RK_Core_Exception
     */
    public function getModule($moduleName)
    {
        if (isset($this->_modules[$moduleName])) {
            return new $this->_modules[$moduleName]();
        }

        throw new \RK_Core_Exception("[$moduleName] module not found");
    }

    /**
     * Возвращает основную валюту приложения
     *
     * @return CurrencyEnum
     */
    public function getAppCurrency()
    {
        return Settings::getInstance('config')->get('app.currency');
    }

    /**
     * Возвращает основной язык приложения
     *
     * @return LanguageEnum
     */
    public function getAppLanguage()
    {
        return Settings::getInstance('config')->get('app.language');
    }
}