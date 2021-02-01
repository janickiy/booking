<?php

namespace App\Services;

use Ixudra\Curl\Facades\Curl;

class MtsSMS
{
    const URL = 'http://mcommunicator.ru/m2m/m2m_api.asmx/SendMessage';
    const API_KEY = '11ccac58-83d2-4f08-a79b-0f022c339014';

    /**
     * @param array $data
     * @return mixed
     */
    public static function sendSMS($data = array()){

        $array = ['msid' => self::formatPhone($data['target']), 'message' => $data['data'], 'naming' => 'trivago'];

        $url = self::URL . '?' . http_build_query($array);

        $result = Curl::to($url)
            ->withHeader('Authorization: Bearer  '. self::API_KEY)
            ->withTimeout()
            ->get();

        $result = trim($result);
        $errors = trans('mts_sms.errors');

        if (isset($errors[$result]))
            return false;
        else
            return true;
    }

    /**
     * @param $phone
     * @return mixed
     */
    private static function formatPhone($phone)
    {
        if(substr($phone, 0, 1) == '+')
            $phone = str_replace('+','',$phone);
        if (substr($phone, 0, 1) == '8')
            $phone = str_replace('8','7',$phone);

        return $phone;
    }
}