<?php

namespace App\Services;

use Ixudra\Curl\Facades\Curl;

class RapportoSMS
{
    const URL = 'http://lk.rapporto.ru:9002/trivago';

    /**
     * @param array $data
     * @return mixed
     */
    public static function sendSMS($data = array()){

        $array = [ 'msisdn' => self::formatPhone($data['target']), 'message' => $data['data']];
        $url = self::URL . '?' . http_build_query($array);

        return Curl::to($url)
                    ->get();
    }

    /**
     * @param $phone
     * @return mixed
     */
    private static function formatPhone($phone)
    {
        if (substr($phone, 0, 1) == '8')
            $phone = str_replace('8','+7',$phone);
        if (substr($phone, 0, 1) != '+')
            $phone = '+'. $phone;

        return $phone;
    }
}