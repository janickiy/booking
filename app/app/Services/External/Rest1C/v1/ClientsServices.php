<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2019
 * Time: 11:44
 */

namespace App\Services\External\Rest1C\v1;


use App\Services\External\Rest1C\Request;

class ClientsServices extends Request
{
    protected static $basePath = '/hs/internal/';

    protected static $version = 't2019';

    protected static $methods = [
        'getclients'
    ];

    protected static $requestTypes = [
        'getclients' => 'POST'
    ];

}