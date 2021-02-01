<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.03.2018
 * Time: 12:12
 */

namespace App\Services\Messages;

//use App\Services\RapportoSMS;
use App\Services\MtsSMS;

/**
 * Class Sms
 * @package App\Services\Messages
 */
class Sms extends Message
{

    /**
     * Номер телефона
     */
    private $target;
    /**
     * {@inheritDoc}
     */
    private $data;
    /**
     * Шаблон
     * @var string
     */
    private $template;

    /**
     * Sms constructor.
     * @param string $target Номер телеофна
     * @param array $data Данные сообщения
     * @param string $template Шаблон
     */
    public function __construct(string $target, string $data, string $template='')
    {
        $this->target = $target;
        $this->data = $data;
        $this->template = $template;
    }

    /**
     * {@inheritDoc}
     */
    public function send()
    {
        return MtsSMS::sendSMS(['target' => $this->target, 'data' => $this->data]);
       // RapportoSMS::sendSMS(['msisdn' => $this->target, 'message' => $this->data]);
    }
}