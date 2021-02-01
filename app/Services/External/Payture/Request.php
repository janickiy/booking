<?php

namespace App\Services\External\Payture;

use App\Services\SessionLog;
use Illuminate\Support\Facades\Redis;
use App\Services\External\Payture\Exceptions\DefaultException;
use App\Helpers\LangHelper;

/**
 * Class Request
 * Базовый абстрактный класс для всех запросов к API "Payture"
 * @package App\Services\External\InnovateMobility
 * @tag
 */
abstract class Request
{
    /**
     * Список доступных методов для конертного POI.
     * @var array
     */
    protected static $methods = [];

    /**
     * Список кешируемых методом и врермя их кеширования в секундах
     * @var array
     */
    protected static $cacheEnabled = [];

    /**
     * Содержит ошибку произошедшую при обработке запроса.
     * @var array|mixed
     */
    protected static $Error;

    /**
     * Метод для вызова магических методов конкретных классов
     * @param $name
     * @param $arguments
     * @return bool|mixed|\Psr\Http\Message\StreamInterface
     */
    public static function __callStatic($name, $arguments)
    {
        $request = str_replace(['get', 'set', 'do'], '', $name);

        if (!in_array($request, static::$methods)) {
            throw new \BadMethodCallException("No method '{$request}' available for Payture API");
        }

        $data = new \stdClass();

        if (isset($arguments[0])) {
            if (!is_array($arguments[0])) {
                throw new \InvalidArgumentException("First argument must be an array");
            }
            $data = $arguments[0];
        }

        return static::doRequest($request, $data);
    }

    /**
     * Выполнения запроса к API
     * @param string $request Вызываемый метод
     * @param array $data Данные отправляемы в API
     * @return bool|mixed|string
     */
    protected static function doRequest($request, $data)
    {
        $startTime = microtime(true);
        $body = self::getCache($request, $data);

        if (!$body) {
            try {
                $response = \Curl::to(config('trivago.services.payture.apiUrl') . $request)
                    ->withData($data)
                    ->withTimeout()
                    ->returnResponseObject()
                    ->post();

                $xml = (array)simplexml_load_string($response->content);

                if (!isset($xml["@attributes"])) {
                    $body =  trans('errors.InternalError');
                    throw new \Exception( trans('errors.InternalError'));
                }

                if (isset($xml["@attributes"]) && $xml["@attributes"]["Success"] == 'False') {

                    $body = LangHelper::trans('references/payture.errors.' . $xml["@attributes"]["ErrCode"]);

                    throw new DefaultException(400, LangHelper::trans('references/payture.errors.' . $xml["@attributes"]["ErrCode"]), config('trivago.services.payture.apiUrl') . $request,
                        json_encode($data, JSON_UNESCAPED_UNICODE), (string)json_encode($xml["@attributes"]));
                }

                $body = $xml["@attributes"];

                self::setCache($request, $data, $body);

            } catch (DefaultException $exception) {
                app('sentry')->captureException($exception);
                logger()->error((string)$exception);

                static::$Error = $body;
                $body = false;
            } catch (\Exception $exception) {
                app('sentry')->captureException($exception);
                logger()->error($exception->getMessage() . ' Code: ' . $exception->getCode());
                static::$Error = $body;
                $body = false;
            }
        }

        $endTime = microtime(true);

        $log = SessionLog::get('external.payture.'.$request, []);

        $log[] = [
            'start' => $startTime,
            'request' => $data,
            'response' => $body ? $body : static::$Error,
            'end' => $endTime,
        ];

        SessionLog::put('external.payture.'.$request, $log);

        return $body;
    }

    /**
     * Получение кешированных данных
     * @param string $request Метод запроса
     * @param array $options Опции запроса
     * @return bool|mixed
     */
    protected  static function getCache($request, $options)
    {
        if (!in_array($request, array_keys(static::$cacheEnabled))) {
            return false;
        }

        $key = md5(json_encode($options));
        return json_decode(Redis::get($request . '.' . $key));
    }

    /**
     * Установка кеша данных
     * @param string $request Метод запроса
     * @param array $options Опции запроса
     * @param array $data Данные ответа для кеширования
     * @return bool
     */
    protected  static function setCache($request, $options, $data)
    {
        if (!in_array($request, array_keys(static::$cacheEnabled))) {
            return false;
        }

        $key = md5(json_encode($options));
        Redis::setex($request . '.' . $key, static::$cacheEnabled[$request], json_encode($data));

        return true;
    }

    /**
     * Получения последней ошибки выполнения запроса
     * @return array|mixed
     */
    public  static function getError()
    {
        return static::$Error;
    }
}