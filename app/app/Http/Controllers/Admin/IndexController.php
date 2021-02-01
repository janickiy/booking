<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 14.08.2018
 * Time: 12:03
 */

namespace App\Http\Controllers\Admin;

use App\Models\References\StatusApi;
use App\Models\{OrdersPayment, User, SessionLog};

class IndexController extends Controller
{

    /**
     * show dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $statusAPI = StatusApi::get();
        $lastUsers = User::orderBy('userId','desc')->limit(15)->get();
        $logs = SessionLog::orderBy('session_log_id','desc')->limit(15)->get();

        return view('admin.index', compact('statusAPI','lastUsers', 'logs'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ordersPaymentInfo($id)
    {
        $payment = OrdersPayment::where('id',$id)->first();

        if (!$payment) abort(404);

        return view('admin.orders_payment_info', compact('payment'));
    }
}