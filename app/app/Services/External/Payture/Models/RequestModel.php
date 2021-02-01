<?php

namespace App\Services\External\Payture\Models;

use Illuminate\Support\Facades\Validator;

/**
 * Class RequestModel
 * @package App\Services\External\InnovateMobility\Models
 */
abstract class RequestModel
{

    protected  $data = [];
    /**
     * Сообщения о ошибках валидации данных
     * @var array
     */
    protected static $validationMessages = [];
    /**
     * Статичные правила проверки данных
     * @var array
     */
    protected static $validationRules = [];
    /**
     * Ошибки валидации
     * @var array
     */
    protected  $validationErrors = [];

    /**
     * RequestModel constructor.
     * @param array $data Данные для модели
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        static::$validationRules = static::getValidationRules();
    }

    /**
     * Проверить данные на валидность
     * @return bool
     */
    public function validate()
    {
        $validator = Validator::make($this->data, static::$validationRules, static::$validationMessages);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors()->toArray();
            return false;
        }

        return true;
    }

    /**
     * Получить тело модели для формирования запроса
     * @return array
     */
    public function getBody()
    {
       return $this->data;
    }

    /**
     * Получить тело модели для формирования запроса в формате строка, содержащая пары ключей и их значений команды, разделённые символом «;» (точка с запятой).
     * Ключи и значения разделены символом «=» (равно).
     * @return array
     */
    public function getBodyToUrlEncoded()
    {
        return $this->data ? urlencode(implode(';', array_map(function ($v, $k) { return sprintf("%s=%s", $k, $v); }, $this->data, array_keys($this->data)))) : '';
    }

    /**
     * Получить тело модели для формирования запроса в формате JSON, закодированная в Base64
     * @return string
     */
    public function getBodyToJSONBase64()
    {
        return $this->data ? base64_encode(json_encode($this->data)) : '';
    }

    /**
     * Сформировать правила проверки данных
     * @return array
     */
    protected static function getValidationRules()
    {
        return [];
    }

    /**
     * Получить ошибки валидации данных (в случае если метод validate вернул false)
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}