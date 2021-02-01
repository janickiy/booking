<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.03.2018
 * Time: 12:11
 */

namespace App\Services\Messages;


/**
 * Class Message
 * Абстрактный класс сообщений
 * @package App\Services\Messages
 */
abstract class Message implements MessageInterface
{
    /**
     * Интедификатор получателя
     * @var string
     */
    private $target;
    /**
     * Данные сообщения
     * @var array
     */
    private $data;
}