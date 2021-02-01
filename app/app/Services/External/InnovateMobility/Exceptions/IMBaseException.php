<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 16.04.2018
 * Time: 12:41
 */

namespace App\Services\External\InnovateMobility\Exceptions;


abstract class IMBaseException extends \Exception
{

    protected $baseMessage;
    protected $method;
    protected $request;
    protected $response;

    public function __construct(int $code, string  $message, string $method, string $request, string $response)
    {
       $fullMessage = "Метод IM API {$method} {$this->baseMessage} {$message} ({$code}) Request: {$request}";
       $this->method = $method;
       $this->request = $request;
       $this->response = $response;

       parent::__construct($fullMessage, $code, null);
    }

    public function __toString()
    {
        return implode("\n",[
            'code: '.$this->code,
            'message: '.$this->message,
            "trace: \n".implode("\n",["\tmethod: ".$this->method,"\trequest: ".$this->request,"\tresponse: ".$this->response])
        ]);

    }
}