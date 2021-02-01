<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Api\ErrorCodesEnv;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Log;
use Throwable;

class BaseController extends Controller
{
    /**
     * @param array|string $errors
     * @param int $status
     * @param int $errorCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(
        $errors,
        $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        $errorCode = ErrorCodesEnv::ERROR_CODE_DEFAULT
    ) {
        if (is_string($errors)) {
            $errors = [$errors];
        }

        return response()->json([
            'success' => false,
            'errorCode' => $errorCode,
            'messages' => $errors
        ], $status);
    }

    protected function successResponse($data = null)
    {
        $success = true;

        if ($data == null) {
            $data = [];
        } elseif (!is_array($data)) {
            $data = [$data];
        }

        $result = array_merge($data, compact('success'));

        return response()->json($result);
    }

    protected function clientFriendlyExceptionResponse(Throwable $e)
    {
        Log::error($e->getMessage());
        throw new AppException(
            env('APP_ENV', '') != 'production' ? ($e->getMessage() . $e->getTraceAsString()) : 'Произошла внутренняя ошибка. Пожалуйста, обратитесь в Техническую поддержку',
            1000,
            [],
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [],
            $e
        );
    }

    protected function appExceptionResponse(AppException $appException) {
        $errors = $appException->getMessage();
        if (is_string($errors)) {
            $errors = [$errors];
        }

        return response()->json([
            'success' => false,
            'errorCode' => $appException->getCode(),
            'messages' => $errors
        ], $appException->getHttpResponseCode());
    }
}
