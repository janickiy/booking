<?php

namespace App\Services\External\RSB;

use App\Services\SessionLog;
use App\Helpers\LangHelper;
use Ixudra\Curl\Facades\Curl;

abstract class Request
{
    /**
     * Список доступных методов для конертного POI.
     * @var array
     */
    protected static $methods = [];

    /**
     * Содержит последнюю ошибку произошедшую при обработке запроса.
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
            throw new \BadMethodCallException("No method '{$request}' available for RSB API");
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
     * @param bool $map Требуеться ли вызывать функцию маппинга результата
     * @return bool|mixed|string
     */
    protected static function doRequest($request,$data)
    {
        $startTime = microtime(true);

        try {

            $response = \Curl::to(config('trivago.services.rsb.paymentUrl'))
                ->withOption('USERAGENT', __CLASS__ . ' HTTP client')
                ->withOption('SSL_VERIFYHOST', false)
                ->withOption('SSL_VERIFYPEER', false)
                ->withOption('SSLKEY', storage_path('key/' . config('trivago.services.rsb.merchantID') . '.key'))
                ->withOption('SSLCERT', storage_path('key/' . config('trivago.services.rsb.merchantID') . '.pem'))
                ->withOption('CAINFO', storage_path('key/chain-ecomm-ca-root-ca.crt'))
                ->withData($data)
                ->withTimeout()
                ->returnResponseObject()
                ->post();

            if (isset($response->status)) {

                $result = self::getResponse($response->content);

                if (substr($response->content, 0, 6) === 'error:') {
                    $body = LangHelper::trans('references/rsb.result_code.' . trim($result["RESULT_CODE"])) . ' error: ' . $result["error"];
                    throw new \Exception($body);
                }

                $body = $result;

            } else {
                throw new \Exception( trans('errors.InternalError'));
            }

        } catch (\Exception $exception) {
            app('sentry')->captureException($exception);
            logger()->error($exception->getMessage() . ' Code: ' . $exception->getCode());
            static::$Error = ["message" => $exception->getMessage(), "code" => $exception->getCode()];
            $body = false;
        }

        $endTime = microtime(true);

        $log = SessionLog::get('external.rsb.' . $request, []);

        $log[] = [
            'start' => $startTime,
            'request' => $data,
            'response' => $body ? $body : static::$Error,
            'end' => $endTime,
        ];

        SessionLog::put('external.rsb.' . $request, $log);

        return $body;
    }

    /**
     * Получения последней ошибки выполнения запроса
     * @return array|mixed
     */
    public static function getError()
    {
        return static::$Error;
    }

    /**
     * Возвращает ссылку для перенаправления на страницу оплаты
     * @param $transID string Идентификатор транзации
     * @return string
     */
    public function getRedirectUrl($transID)
    {
        return urlencode(config('trivago.services.rsb.redirectUrl') . $transID);
    }

    /**
     * Обработчик ответов
     * @param string $response
     * @return array
     */
    private static function getResponse($response = '')
    {
        if (preg_match_all('#(.*)\:(.*)(?:\n|$)#Uis', $response, $matches)) {
            foreach ($matches[1] as $key => $val) {
                $result[$val] = trim($matches[2][$key]);
            }

            return $result;
        }

        return [];
    }
}