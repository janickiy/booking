<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.04.2018
 * Time: 17:09
 */

namespace App\Services\External\InnovateMobility;


use App\Helpers\LangHelper;
use App\Services\External\InnovateMobility\Exceptions\IMAuthException;
use App\Services\External\InnovateMobility\Exceptions\IMBadRequestException;
use App\Services\External\InnovateMobility\Exceptions\IMBaseException;
use App\Services\External\InnovateMobility\Exceptions\IMDefaultException;
use App\Services\External\InnovateMobility\Exceptions\IMRailwayNoTrainsException;
use App\Services\SessionLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;

/**
 * Class Request
 * Базовый абстрактный класс для всех запросов к API "Инновационная Мобильность"
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
     * Список доступных методов для конертного POI.
     * @var array
     */
    protected static $methods = [];

    /**
     * Возвращенно ли из кеша
     * @var bool
     */
    protected static $formCache = true;

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
        $request = str_replace(['get', 'set', 'do'], '', $name);
        if (!in_array($request, static::$methods)) {
            throw new \BadMethodCallException("No method '{$request}' available for IM v1 References API");
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

        $body = self::getCache($request, $data);

        if (!$body) {
            static::$formCache = false;

            if (static::$basePath === 'Railway/V1/Search/' || static::$basePath === 'Info/V1/References/') {
                $apiEndpoint = config('trivago.services.im.searchApiUrl');
                $header = [
                    'Pos' => config('trivago.services.im.searchLogin'),
                    'Content-Type' => 'application/json',
                    'Accept-Encoding' => 'gzip',
                    'Authorization' => 'Basic ' . base64_encode(config('trivago.services.im.searchLogin') . ':' . config('trivago.services.im.searchPassword'))
                ];
            } else {
                $apiEndpoint = config('trivago.services.im.apiUrl');
                $header = [
                    'Pos' => config('trivago.services.im.login'),
                    'Content-Type' => 'application/json',
                    'Accept-Encoding' => 'gzip',
                    'Authorization' => 'Basic ' . base64_encode(config('trivago.services.im.login') . ':' . config('trivago.services.im.password'))
                ];
            }

            $client = new Client([
                'base_uri' => $apiEndpoint,
                'http_errors' => false,
            ]);

            try {
                $response = $client->post(static::$basePath . $request, [
                    'headers' => $header,
                    'body' => json_encode($data)
                ]);
                $contentType = $response->getHeader('Content-type');
                if (strpos($contentType[0], 'json') !== false) {
                    $body = json_decode((string)$response->getBody(), false, 512,
                        JSON_UNESCAPED_UNICODE & JSON_UNESCAPED_SLASHES);
                } else {
                    $body = $response->getBody();
                }

                $responseCode = (int)$response->getStatusCode();

                switch (true) {
                    case $responseCode == 400:
                        throw new IMBadRequestException($body->Code, $body->Message, static::$basePath . $request,
                            json_encode($data, JSON_UNESCAPED_UNICODE & JSON_UNESCAPED_SLASHES),
                            (string)$response->getBody());
                    case $responseCode > 400 && $responseCode < 404:
                        throw new IMAuthException($body->Code, $body->Message, static::$basePath . $request,
                            json_encode($data, JSON_UNESCAPED_UNICODE), (string)$response->getBody());
                    case $responseCode >= 500:
                        if ($body->Code == 43) {
                            throw new IMBadRequestException($body->Code, $body->Message, static::$basePath . $request,
                                json_encode($data, JSON_UNESCAPED_UNICODE & JSON_UNESCAPED_SLASHES),
                                (string)$response->getBody());
                        } elseif ($body->Code == 310 || $body->Code == 311) {
                            throw new IMRailwayNoTrainsException($body->Code, $body->Message,
                                static::$basePath . $request,
                                json_encode($data, JSON_UNESCAPED_UNICODE & JSON_UNESCAPED_SLASHES),
                                (string)$response->getBody());
                        }
                        throw new IMDefaultException($body->Code, $body->Message, static::$basePath . $request,
                            json_encode($data, JSON_UNESCAPED_UNICODE), (string)$response->getBody());
                }

                self::setCache($request, $data, $body);

            } catch (IMRailwayNoTrainsException $exception) {
                static::$lastError = $body;
                $body = false;
            } catch (IMBaseException $exception) {
                app('sentry')->captureException($exception);
                logger()->error((string)$exception);
                static::$lastError = $body;
                $body = false;
            } catch (\Exception $exception) {
                app('sentry')->captureException($exception);
                logger()->error($exception->getMessage() . ' Code: ' . $exception->getCode());
                $body = false;
            }
        }

        $mapMethod = 'map' . $request;
        $unmapedResponse = $body;

        if ($body && $map && method_exists(static::class, $mapMethod)) {
            $body = static::$mapMethod($body, $mapOptions);
        }

        $endTime = microtime(true);
        $log = SessionLog::get('external.im.' . $request, []);
        $log[] = [
            'start' => $startTime,
            'fromCache' => static::$formCache ? 'true' : 'false',
            'request' => $data,
            'response' => $unmapedResponse ? $unmapedResponse : static::$lastError,
            'end' => $endTime,
        ];
        SessionLog::put('external.im.' . $request, $log);

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
        $error = static::$lastError;
        $section = 'commonErrorCodes';

        switch (true) {
            case ($error->Code > 0 && $error->Code < 200):
                $section = 'commonErrorCodes';
                break;
            case ($error->Code > 300 && $error->Code < 400) || ($error->Code > 1300 && $error->Code < 1400):
                $section = 'railwayErrorCodes';
                break;
            case ($error->Code > 200 && $error->Code < 300):
                $section = 'aviaErrorCodes';
                break;
            case ($error->Code > 500 && $error->Code < 600):
                $section = 'busErrorCodes';
                break;
            case ($error->Code > 600 && $error->Code < 700):
                $section = 'aeroexpressErrorCodes';
                break;
        }

        $key = "references/im.{$section}.{$error->Code}";
        $messageByLocale = LangHelper::trans($key, $error->MessageParams);

        $error->Message = $messageByLocale!=$key ? $messageByLocale : $error->Message;
        return $error;
    }
}