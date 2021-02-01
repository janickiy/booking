<?php

namespace App\Exceptions;

use App\Contracts\Api\ErrorCodesEnv;
use RuntimeException;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppException extends RuntimeException
{
    protected $context;
    protected $errorCode;
    protected $httpResponseCode;
    protected $httpResponseHeaders;
    protected $messages;

    public function __construct(
        $messages = [],
        int $errorCode = ErrorCodesEnv::ERROR_CODE_DEFAULT,
        array $context = [],

        int $httpResponseCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $httpResponseHeaders = [],

        Throwable $previous = null
    ) {
        $this->messages = (array) $messages;
        $this->errorCode = $errorCode;
        $this->context = $context;

        $this->httpResponseCode = $httpResponseCode;
        $this->httpResponseHeaders = $httpResponseHeaders;

        parent::__construct(implode(';', $this->messages), $this->errorCode, $previous);
    }

    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }

    public function render(Request $request = null)
    {
        if ($request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errorCode' => $this->errorCode,
                    'messages' => $this->messages
                ], $this->httpResponseCode, $this->httpResponseHeaders);
            } else {
                // @todo Normal response
            }
        }

        return false;
    }
}
