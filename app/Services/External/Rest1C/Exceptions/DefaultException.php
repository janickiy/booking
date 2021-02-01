<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 08.04.2019
 * Time: 14:37
 */

namespace App\Services\External\Rest1C\Exceptions;


class DefaultException extends BaseException
{
    protected $baseMessage = 'вернул ошибку: ';
}