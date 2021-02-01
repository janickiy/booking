<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.05.2018
 * Time: 9:32
 */

namespace App\Services\External\InnovateMobility\Models;

use Illuminate\Support\Facades\Validator;


/**
 * Class RequestModel
 * @package App\Services\External\InnovateMobility\Models
 */
abstract class RequestModel
{
    /**
     * Тип модели в системе API
     * @var string
     */
    protected static $type;
    /**
     * Данные модели
     * @var array
     */
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
       return static::$type ? array_merge(['$type' => static::$type], $this->data) : $this->data;
       // return array_merge(['$type' => static::$type], $this->data);
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