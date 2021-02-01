<?php

namespace App\Services\External\InnovateMobility\v1;

use App\Services\External\InnovateMobility\Request;

class OrderInfo extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Order/V1/Info/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'OrderInfo',
        'OrderList',
    ];
}