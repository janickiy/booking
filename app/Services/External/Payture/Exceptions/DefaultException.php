<?php

namespace App\Services\External\Payture\Exceptions;

class DefaultException extends BaseException
{
    protected $baseMessage = 'вернул ошибку: ';
}