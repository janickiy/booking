<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 16.04.2018
 * Time: 13:14
 */

namespace App\Services\External\InnovateMobility\Exceptions;


class IMDefaultException extends IMBaseException
{
    protected $baseMessage = 'вернул ошибку: ';
}