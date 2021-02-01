<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.03.2018
 * Time: 12:13
 */

namespace App\Services\Messages;


/**
 * Interface MessageInterface
 * @package App\Services\Messages
 */
interface MessageInterface
{
    /**
     * Метод отправки сообщений
     * @return mixed
     */
    public function send();
}