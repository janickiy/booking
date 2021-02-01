<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.03.2018
 * Time: 12:12
 */

namespace App\Services\Messages;

/**
 * Class Email
 * @package App\Services\Messages
 */
class Email extends Message
{
    /**
     * Адресс электронной почты получателя
     * @var string
     */
    private $target;
    /**
     * Адресс электронной почты отправителя
     * @var string
     */
    private $source;

    /**
     * Тема письма
     * @var string
     */
    private $subject;
    /**
     * {@inheritDoc}
     */
    private $data;
    /**
     * Путь к файлу шаблона
     * @var string
     */
    private $template;

    /**
     * Файлы вложений
     * @var array
     */
    private $attachments;

    /**
     * Email constructor.
     * @param string $to Адресс электронной почты получателя
     * @param string $from Адресс электронной почты отправителя
     * @param string $subject Заголовок письма
     * @param mixed $data Данные письма
     * @param string $template Путь к файлу шаблона
     * @param array $attachments Файлы вложений
     */
    public function __construct(string $to, string $from, string $subject, $data, string $template = '', array $attachments = [])
    {
        $this->target = $to;
        $this->source = $from;
        $this->subject = $subject;
        $this->data = $data;
        $this->template = $template;
        $this->attachments = $attachments;
    }

    /**
     * Отправляет письмо
     * @return mixed|void
     */
    public function send()
    {
        $func = function (\Illuminate\Mail\Message $message) {
            $message->to($this->target);
            $message->from($this->source);
            $message->subject($this->subject);
            foreach ($this->attachments as $attachment) {
                $message->attach($attachment);
            }
        };

        if ($this->template != '') {

            \Mail::send($this->template, $this->data, $func);


        } else {

            \Mail::raw($this->data, $func);
        }
    }
}