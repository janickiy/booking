<?php

namespace ReservationKit\src\Modules\Base\Model;

// WSSE библиотека
require_once(dirname(__FILE__) . '/WSSE/soap-wsse.php');
// Используется из-за наличия функции записи логов
global $CONFIG;
require_once $CONFIG[site_dir].'common/scripts/phpfuncs.php';

class RK_Base_SoapClient extends SoapClient // TODO в phalanger реализованы не все необходимые Soap-методы
{

}