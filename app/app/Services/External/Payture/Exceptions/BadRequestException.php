<?php

namespace App\Services\External\Payture\Exceptions;

class BadRequestException extends BaseException
{
    protected $baseMessage = 'ошибка ввода: ';
}