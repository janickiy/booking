<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.04.2018
 * Time: 17:09
 */

namespace App\Services\External\Soap1c;


use App\Helpers\XMLHelpers;
use App\Services\SessionLog;
use Illuminate\Support\Facades\Redis;

/**
 * Class Request
 * Базовый абстрактный класс для всех запросов к API "1С"
 * @package App\Services\External\InnovateMobility
 * @tag
 */
abstract class Request
{

    /**
     * Путь к POI общего для всех методов API.
     * @var string
     */
    protected static $basePath = '';

    /**
     * Весрия API для текущих запросов
     * @var string
     */
    protected static $version = '';

    /**
     * Список доступных методов для конертного POI.
     * @var array
     */
    protected static $methods = [];

    /**
     * Список кешируемых методом и вермя их кеширования в секундах (например 'TrainPricing' => 60).
     * @var array
     */
    protected static $cacheEnabled = [];

    /**
     * Содержит последнюю ошибку произошедшую при обработке запроса.
     * @var array|mixed
     */
    protected static $lastError;

    /**
     * Метод для вызова магических методов конкретных классов
     * @param $name
     * @param $arguments
     * @return bool|mixed|\Psr\Http\Message\StreamInterface
     */
    public static function __callStatic($name, $arguments)
    {
        $request = str_replace(['get','set','do'], '', $name);
        if (!in_array($request, static::$methods)) {
            throw new \BadMethodCallException("No method '{$request}' available for 1с v2 API");
        }

        $map = false;
        $mapOptions = [];
        $data = new \stdClass();

        if (isset($arguments[0])) {
            if (!is_array($arguments[0])) {
                throw new \InvalidArgumentException("First argument must be an array");
            }
            $data = $arguments[0];
        }

        if (isset($arguments[1])) {
            if (!is_bool($arguments[1])) {
                throw new \InvalidArgumentException("Second argument must be an boolean");
            }
            $map = $arguments[1];
        }

        if (isset($arguments[2])) {
            if (!is_array($arguments[2])) {
                throw new \InvalidArgumentException("Third argument must be an array");
            }
            $mapOptions = $arguments[2];
        }

        return static::doRequest($request, $data, $map, $mapOptions);
    }

    /**
     * Выполнения запроса к API
     * @param string $request Вызываемый метод
     * @param array $data Данные отправляемы в API
     * @param bool $map Требуеться ли вызывать функцию маппинга результата
     * @param array $mapOptions Опции для функции маппинга результата
     * @return bool|mixed|string
     */
    protected static function doRequest($request, $data, $map = false, $mapOptions = [])
    {
        set_time_limit(300);

        $startTime = microtime(true);

        $apiEndpoint = config('trivago.services.soap1c.apiUrl');


        $body = self::getCache($request, $data);

        if (!$body) {
            try {

                $client =  new \SoapClient($apiEndpoint.static::$version."/ws/".static::$basePath."?wsdl", ['trace' => true, 'exception' => true]);
                $options = XMLHelpers::array2XML($data);
                $response = $client->$request(['content' => $options]);
                if($response->return!='OK') {
                    $body = new \SimpleXMLElement($response->return, LIBXML_NOERROR);
                }else{
                    $body = true;
                }

                self::setCache($request, $data, $body);

            } catch (\Exception $exception) {
                app('sentry')->captureException($exception);
                logger()->error($exception->getMessage() . ' Code: ' . $exception->getCode());
                static::$lastError = ["message" => $exception->getMessage(),"code"=>$exception->getCode()];
                $body = false;
            }
        }

        $mapMethod = 'map' . $request;
        $unmapedResponse = $body;

        if ($body && $map && method_exists(static::class, $mapMethod)) {
            $body = static::$mapMethod($body, $mapOptions);
        }

        $endTime = microtime(true);

        SessionLog::put('external.soap1c.'.$request, [
            'start'=> $startTime,
            'request' => $data,
            'response' => $unmapedResponse ? $unmapedResponse : static::$lastError,
            'end' =>$endTime,
        ]);

        return $body;
    }

    /**
     * Получение кешированных данных
     * @param string $request Метод запроса
     * @param array $options Опции запроса
     * @return bool|mixed
     */
    protected static function getCache($request, $options)
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
    protected static function setCache($request, $options, $data)
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
    public static function getLastError()
    {
        return static::$lastError;
    }
}