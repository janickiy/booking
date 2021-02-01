<?php

namespace App\Models\Admin\Hotel;

use Trivago\Hotels\Modules\Models\Order;

class HotelOrders extends Order
{
    protected $table = 'orders_hotels';
}
