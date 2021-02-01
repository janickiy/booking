<?php

namespace App\Jobs;

use App\Models\OrdersRailway;
use App\Services\External\Soap1c\v2\RailwayOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Soap1CRailwayOrderRefund implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $orderId;
    private $orderVoidedBlanks;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId, $orderVoidedBlanks)
    {
        $this->orderId = $orderId;
        $this->orderVoidedBlanks = $orderVoidedBlanks;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = OrdersRailway::find($this->orderId);
        $order->is_1c_sync = 'false';
        $response = RailwayOrder::Refund($order, $this->orderVoidedBlanks);

        if($response){
            $order->is_1c_sync = 'true';
            $order->save();
        }
    }
}
