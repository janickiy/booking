<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.02.2018
 * Time: 10:34
 */

namespace App\Helpers;


class ResponseHelpers
{
    public static function jsonResponse($data, $code = 200, $cors=false)
    {
        $host = str_replace(['http://', 'https://', '/'], '', request()->headers->get('Origin'));

        $headers = [
            'Content-type' => 'application/json'
        ];

        if($cors && in_array($host, config('app.domains'))){
            $headers['Access-Control-Allow-Origin'] = request()->headers->get('Origin');
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        return response(
            json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            $code,
            $headers
        );
    }

}