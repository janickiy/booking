<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 16.04.2018
 * Time: 12:35
 */

namespace App\Services\External\InnovateMobility\Exceptions;


use Throwable;

class IMBadRequestException extends IMBaseException
{
    protected $baseMessage = 'ошибка ввода: ';
}