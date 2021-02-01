<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.03.2019
 * Time: 10:09
 */

namespace App\Jobs;

use App\Models\OrdersAeroexpress;
use App\Models\OrdersRailway;
use App\Services\External\Soap1c\v2\RailwayOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Soap1CRailwayOrderPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $orderId;
    private $type;

    public function __construct($orderId, $type)
    {
        $this->orderId = $orderId;
        $this->type = $type;
    }

    public function handle()
    {
        if ($this->type == 'railway') {
            $order = OrdersRailway::find($this->orderId);
        } else {
            $order = OrdersAeroexpress::find($this->orderId);
        }
        $response = RailwayOrder::Ticket($order);

        if($response){
            $order->is_1c_sync = 'true';
            $order->save();
        }
    }

}