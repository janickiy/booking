<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Models\{Orders, OrdersLog, OrdersRailway, OrdersAeroexpress, OrdersBus, OrdersAvia};
use App\Helpers\StringHelpers;
use URL;

class OrdersController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('admin.orders.list')->with('title','Заказы');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user('web');

        $validator = Validator::make($request->all(), [
            'orderItems' => 'required|array',
        ]);

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => [$validator->errors()]
            ], 400);
        }

        $data['orderItems'] = [$request->orderItems];
        $data['userId'] = $user->userId;
        $data['orderStatus'] = 0;
        $data['totalAmount'] = 0;

        $insertId = Orders::create($data)->id;

        if (!$insertId) {
            return ResponseHelpers::jsonResponse([
                'error' => 'Ошибка при создания заказа!'
            ], 500);
        }

        return ResponseHelpers::jsonResponse(['result' => true, 'order' => $insertId]);
    }

    /**
     * @param $status
     * @param $id
     */
    public function changeOrderStstus($status, $id)
    {
        Orders::where('id',$id)->update('orderStatus',$status);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function railwayInfo($id)
    {
        $order = OrdersRailway::where('orderId', $id)->first();

        if ($order) {
            $passengers = StringHelpers::ObjectToArray($order->passengersData);
            $orderData = StringHelpers::ObjectToArray($order->orderData);
            $documents = StringHelpers::ObjectToArray($order->orderDocuments);

            $logs = OrdersLog::where('order_id',$id)->get();

            return view('admin.orders_railway.info', compact('order', 'passengers', 'references', 'documents', 'orderData', 'id', 'logs'))->with('title', 'Подробности');
        }

        abort(404);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function aeroexpressInfo($id)
    {
        $order = OrdersAeroexpress::where('orderId', $id)->first();

        if ($order) {
            $passengers = StringHelpers::ObjectToArray($order->passengersData);
            $orderData = StringHelpers::ObjectToArray($order->orderData);
            $documents = StringHelpers::ObjectToArray($order->orderDocuments);
            $logs = OrdersLog::where('order_id',$id)->get();
            return view('admin.orders_aeroexpress.info', compact('order', 'passengers', 'references', 'documents', 'orderData', 'id', 'logs'))->with('title', 'Подробности');
        }

        abort(404);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function busInfo($id)
    {
        $order = OrdersBus::where('orderId', $id)->first();

        if ($order) {
            $logs = OrdersLog::where('order_id',$id)->get();
            $passengers = StringHelpers::ObjectToArray($order->passengersData);
            $orderData = StringHelpers::ObjectToArray($order->orderData);
            $documents = StringHelpers::ObjectToArray($order->orderDocuments);

            return view('admin.orders_bus.info', compact('order', 'passengers', 'references', 'documents', 'orderData', 'id','logs'))->with('title', 'Подробности');
        }

        abort(404);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function busAvia($id)
    {
        $order = OrdersAvia::where('orderId', $id)->first();
        $bookingData = StringHelpers::ObjectToArray($order->booking_data);

        if ($order) {
            return view('admin.orders_bus.info', compact('bookingData', 'id'))->with('title', 'Подробности');
        }

        abort(404);
    }


}