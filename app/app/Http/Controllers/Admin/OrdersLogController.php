<?php

namespace App\Http\Controllers\Admin;

use App\Models\OrdersLog;

class OrdersLogController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list($orderId)
    {
        return view('admin.orders_log.list', compact('orderId'))->with('title','Лог заказов');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function info($id)
    {
        $log = OrdersLog::find($id);

        if (!$log) abort(404);

        return view('admin.orders_log.info', compact('log'))->with('title','Полезная нагрузка');
    }
}