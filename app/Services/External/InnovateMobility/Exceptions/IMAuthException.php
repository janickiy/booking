<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 16.04.2018
 * Time: 13:12
 */

namespace App\Services\External\InnovateMobility\Exceptions;


class IMAuthException extends IMBaseException
{
    protected $baseMessage = 'ошибка авторизации: ';
}