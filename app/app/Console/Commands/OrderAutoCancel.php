<?php

namespace App\Console\Commands;

use App\Models\OrdersRailway;
use Illuminate\Console\Command;

class OrderAutoCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:orders-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        OrdersRailway::whereIn('orderStatus',[OrdersRailway::ORDER_STATUS_RESERVED,OrdersRailway::ORDER_STATUS_CREATED])->where('payTill','<',date('Y-m-d H:i:s'))->update(['orderStatus' => -1]);
    }
}
