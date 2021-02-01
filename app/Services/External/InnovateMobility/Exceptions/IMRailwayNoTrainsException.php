<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 19.04.2018
 * Time: 16:44
 */

namespace App\Services\External\InnovateMobility\Exceptions;


class IMRailwayNoTrainsException extends IMBaseException
{
    protected $baseMessage = 'нет информации: ';
}