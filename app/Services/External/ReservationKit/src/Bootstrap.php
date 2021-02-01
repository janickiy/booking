<?php

use ReservationKit\src\RK;
use ReservationKit\src\Modules\Core\Model\Enum\LanguageEnum;
use ReservationKit\src\Modules\Core\Model\Enum\CurrencyEnum;

date_default_timezone_set('Europe/Moscow');
set_time_limit(90);

function ReservationKit_Bootstrap($kitPath) {
    if (!defined('RK_ROOT_PATH')) {
        define('RK_ROOT_PATH', $kitPath);

        define('SYSTEM_NAME_S7AGENT', 's7agent');           // TODO заменить в S7Agent на маленькие буквы и проверить
        define('SYSTEM_NAME_GALILEO_UAPI', 'galileo');      // TODO избавиться от приставки UAPI
        define('SYSTEM_NAME_SIRENA', 'sirena');
    }

    require_once($kitPath . '/functions.php');       // Библиотека глобальных функций
    require_once($kitPath . '/ReservationKit.php');  // Главный класс приложения
    require_once($kitPath . '/Autoloader.php');      // Автозагрузчик классов

    function __RK_autoload($className) {
        RK_Autoloader::getInstance()->loadClass($className);
    }

    spl_autoload_register('__RK_autoload');
}

ReservationKit_Bootstrap(dirname(__FILE__));

$siteHost   = isset($dbHost) ? $dbHost : 'trivago.localhost';
$siteScheme = isset($siteScheme) ? $siteScheme : 'http';

$dbHost = isset($dbHost) ? $dbHost : '';
$dbPort = isset($dbPort) ? $dbPort : '';
$dbName = isset($dbName) ? $dbName : '';
$dbUser = isset($dbUser) ? $dbUser : '';
$dbPass = isset($dbPass) ? $dbPass : '';

// Инициализация
RK::init(array(
    'app' => array(
        'language' => LanguageEnum::RU,
        'currency' => CurrencyEnum::RUB,
        //'process_worker_url' => 'http://' . 'trivago.localhost' . '/rkworker.php?wn={WORKER}'
        'process_worker_url' => $siteScheme . '://' . $siteHost . '/api/v1/avia/rkworker?wn={WORKER}'
    ),

    'db' => array(
        'main' => array(
            'type'     => 'pgsql',
            'host'     => $dbHost,
            'port'     => $dbPort,
            'dbname'   => $dbName,
            'username' => $dbUser,
            'password' => $dbPass
        ),
        'catalog' => array(
            'type'     => 'pgsql',
            'host'     => $dbHost,
            'port'     => $dbPort,
            'dbname'   => $dbName,
            'username' => $dbUser,
            'password' => $dbPass
        ),
        /*
        'catalog2' => array(
            'type'     => 'mssql',
            'host'     => '127.0.0.1',
            'dbname'   => 'trivago',
            'username' => 'sa',
            'password' => 'We7115KjulvE'
        )
        */
    )
));
